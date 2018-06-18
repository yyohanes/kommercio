<?php

namespace Kommercio\Http\Controllers\Api\Frontend\Order;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Kommercio\Events\CouponEvent;
use Kommercio\Events\OrderEvent;
use Kommercio\Events\OrderUpdate;
use Kommercio\Facades\AddressHelper;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Facades\NewsletterSubscriptionHelper;
use Kommercio\Facades\OrderHelper;
use Kommercio\Facades\PriceFormatter;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Requests\Api\Order\ShippingMethodsFormRequest;
use Kommercio\Http\Requests\Api\Order\OrderFormRequest;
use Kommercio\Http\Resources\Order\DisabledDateCollection;
use Kommercio\Http\Resources\Order\OrderLimitResource;
use Kommercio\Http\Resources\Order\OrderResource;
use Kommercio\Http\Resources\PaymentMethod\PublicPaymentMethodResource;
use Kommercio\Http\Resources\ShippingMethod\ShippingOptionCollection;
use Kommercio\Models\Customer;
use Kommercio\Models\Order\LineItem;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Order\Payment;
use Kommercio\Models\PaymentMethod\PaymentMethod;
use Kommercio\Models\Product;
use Kommercio\Models\ProductCategory;
use Kommercio\Models\ShippingMethod\ShippingMethod;

class OrderController extends Controller {

    /**
     * Get available shipping methods
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function shippingMethods(ShippingMethodsFormRequest $request) {
        // If products is passed in, simulate free edit dummy order
        if ($request->filled('products')) {
            $lineItemsData = [];
            foreach ($request->input('products', []) as $key => $productId) {
                $product = Product::findById($productId);

                $quantity = $request->input('quantities.' . $key, 1);

                $lineItemsData[] = [
                    'line_item_id' => $productId,
                    'line_item_type' => 'product',
                    'quantity' => $quantity,
                    'net_price' => $product->getNetPrice(),
                ];
            }

            $request->replace(array_merge(
                $request->input(),
                [
                    'line_items' => $lineItemsData,
                ]
            ));
        }

        $order = OrderHelper::createDummyOrderFromRequest($request);

        $getShippingOptions = [
            'order' => $order,
            'request' => $request,
            'frontend' => TRUE,
        ];

        // If shipping_method is passed, only get options from that shipping method
        if ($request->filled('shipping_method')) {
            $shippingMethod = ShippingMethod::findById($request->input('shipping_method'));
            $shippingOptions = $shippingMethod->getPrices($getShippingOptions);
        } else {
            $shippingOptions = ShippingMethod::getShippingMethods($getShippingOptions);
        }

        $collection = new Collection();

        foreach($shippingOptions as $machineName => $shippingOption){
            $price = $shippingOption['price']['amount'] ?? 0;
            $taxPrice = $price;

            if ($shippingOption['taxable']) {
                $taxOptions = [];

                if ($request->get('store_id')) {
                    $taxOptions['store_id'] = $request->get('store_id');
                }

                $taxPrice = PriceFormatter::getTaxPrice(
                    $price,
                    $taxOptions
                );
            }

            $shippingOption['machine_name'] = $machineName;
            $shippingOption['price']['amount_with_tax'] = $taxPrice;

            $collection->push($shippingOption);
        }

        $response = new ShippingOptionCollection($collection);

        return $response->response();
    }

    /**
     * Get available shipping methods
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function paymentMethods(Request $request) {
        $order = OrderHelper::createDummyOrderFromRequest($request);

        $paymentMethods = PaymentMethod::getPaymentMethods([
            'frontend' => true,
            'order' => $order,
            'request' => $request,
        ]);

        $paymentMethodOptions = [];
        foreach($paymentMethods as $paymentMethod){
            $paymentMethodOptions[$paymentMethod->id] = $paymentMethod->name;
        }

        $response = PublicPaymentMethodResource::collection($paymentMethods);

        return $response->response();
    }

    public function submit(OrderFormRequest $request) {
        /*
         * There are 2 passes of validation:
         * 1. When OrderFormRequest in function argument is resolved
         * 2. After 1st one is passed because it requires Order instance to be created
         */

        $dummyOrder = OrderHelper::createDummyOrderFromRequest($request);

        $furtherRules = OrderFormRequest::getFurtherRules($request, $dummyOrder);
        $this->validate($request, $furtherRules);

        $deliveryDateIsOn = ProjectHelper::getConfig('enable_delivery_date', FALSE);

        $order = OrderHelper::createEmptyOrder($request);
        $paymentMethod = PaymentMethod::findById($request->input('payment_method'));
        $currency = CurrencyHelper::getCurrentCurrency()['code'];
        $store = ProjectHelper::getStoreByRequest($request);
        $customer = Customer::findById($request->input('customer_id'));
        $customerProfile = $customer->getProfile();

        $orderData = [
            'payment_method_id' => $paymentMethod->id,
            'currency' => $currency,
            'conversion_rate' => 1,
            'notes' => $request->input('notes', null),
        ];

        if ($deliveryDateIsOn) {
            $orderData['delivery_date'] = $request->input('delivery_date');
        }

        $order->fill($orderData);
        $order->store()->associate($store);
        $order->customer()->associate($customer);
        $order->save();

        $products = [];
        $quantities = [];
        foreach ($request->input('products', []) as $key => $productId) {
            $product = Product::findById($productId);
            $quantity = $request->input('quantities.' . $key, 1);

            $products[$product->id] = $product;
            $quantities[$product->id] = $quantity;
        }

        // Add products to cart
        foreach ($products as $product) {
            $quantity = $quantities[$product->id];
            $children = [];
            $composites = $request->input('products_children.' . $product->id, []);

            foreach ($composites as $compositeId => $composite) {
                foreach ($composite as $childProductId => $childQuantity) {
                    if (empty($childQuantity) || $childQuantity <= 0) continue;

                    $children[$compositeId][] = [
                        'quantity' => $childQuantity,
                        'product_id' => $childProductId,
                    ];
                }
            }

            $order->addToCart(
                $product,
                $quantity,
                [
                    'children' => $children,
                ]
            );
        }

        // Currently, our priority is correct delivery information.
        $shippingProfileDetails = $customerProfile->getDetails();
        $shippingProfileDetails['full_name'] = $request->input('shippingProfile.full_name', null);
        $shippingProfileDetails['email'] = $request->input('shippingProfile.email', null);
        $shippingProfileDetails['phone_number'] = $request->input('shippingProfile.phone_number', null);
        $shippingProfileDetails['address_1'] = $request->input('shippingProfile.address_1', null);
        $shippingProfileDetails['address_2'] = $request->input('shippingProfile.address_2', null);
        $shippingProfileDetails['postal_code'] = $request->input('shippingProfile.postal_code', null);
        $shippingProfileDetails['country_id'] = $request->input('shippingProfile.country_id', null);
        $shippingProfileDetails['state_id'] = $request->input('shippingProfile.state_id', null);
        $shippingProfileDetails['city_id'] = $request->input('shippingProfile.city_id', null);
        $shippingProfileDetails['custom_city'] = $request->input('shippingProfile.custom_city', null);
        $shippingProfileDetails['district_id'] = $request->input('shippingProfile.district_id', null);
        $shippingProfileDetails['area_id'] = $request->input('shippingProfile.area_id', null);

        // Only billing name & phone number is overridable. If address infos are all empty, override with shipping.
        $billingProfileDetails = $this->isProfileOverrideable($customerProfile->getDetails())
            ? $shippingProfileDetails
            : $customerProfile->getDetails();

        $billingProfileDetails = array_merge(
            $billingProfileDetails,
            [
                'full_name' => $request->input('billingProfile.full_name', null),
                'phone_number' => $request->input('billingProfile.phone_number', null),
            ]
        );

        $order->saveProfile('billing', $billingProfileDetails);
        $order->saveProfile('shipping', $shippingProfileDetails);
        $order->updateShippingMethod($request->input('shipping_option'));

        try {
            $this->placeOrder($request, $order);

            // Final process payment
            $paymentResult = $this->processFinalPayment(
                $order,
                $paymentMethod,
                $request
            );
        } catch (\Throwable $e) {
            report($e);
        }

        if ($request->input('_subscribe_newsletter', false) && $request->filled('billingProfile.email')) {
            try {
                NewsletterSubscriptionHelper::subscribe(
                    'default',
                    $request->input('billingProfile.email'),
                    $request->input('billingProfile.full_name', null)
                );
            } catch (\Throwable $e) {
                \Log::error($e);
            }
        }

        if (is_array($paymentResult)) {
            return new JsonResponse(
                [
                    'errors' => $paymentResult,
                ],
                422
            );
        }

        $placedOrder = $this->processPlaceOrder($request, $order);

        $response = new OrderResource($placedOrder);

        return $response->response();
    }

    /**
     * Check dates availability of a month given products quantity
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function availability(Request $request) {
        $rules = [
            'products' => [
                'required',
                'array',
            ],
            'products.*' => [
                'required',
                'integer',
            ],
            'dates' => [
                'required',
                'string',
            ],
        ];

        $this->validate($request, $rules);

        // Form months array
        $dates = explode(',', $request->get('dates', ''));

        $products = [];
        $disabledDates = [];
        $orderedQuantities = [];

        foreach ($request->get('products', []) as $productId => $orderedQuantity) {
            $product = Product::findById($productId);

            if (!$product) continue;

            $products[$productId] = $product;
            $orderedQuantities[$productId] = $orderedQuantity;
        }

        $store = ProjectHelper::getStoreByRequest($request);

        $storeId = $store->id;

        $options = [
            'store_id' => $storeId,
            'quantity' => $orderedQuantity,
            'dates' => $dates,
            'productLineItems' => [],
        ];

        foreach ($products as $productId => $product) {
            $orderedQuantity = $orderedQuantities[$productId];

            $lineItemOption = [
                'line_item_type' => 'product',
                'net_price' => $product->getNetPrice(),
                'quantity' => $orderedQuantity,
                'sku' => $product->sku,
            ];

            $lineItem = new LineItem();
            $lineItem->processData($lineItemOption);

            $options['productLineItems'][] = $lineItem;
        }

        foreach ($products as $productId => $product) {
            $productDisabledDates = $product->getUnavailableDeliveryDates($options);

            $disabledDates = array_merge($disabledDates, $productDisabledDates);
        }

        $disabledDateCollection = collect($disabledDates);
        $disabledDateCollection = $disabledDateCollection->unique();

        $response = new DisabledDateCollection($disabledDateCollection->values());

        return $response
            ->response()
            ->withHeaders([
                'Cache-Control' => 'max-age=0, no-cache, must-revalidate, proxy-revalidate'
            ]);
    }

    /**
     * Get order limit of a store
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getOrderLimit(Request $request) {
        $date = $request->get('date', Carbon::now()->format('Y-m-d'));
        $productId = $request->get('product');
        $productCategoryId = $request->get('product_category');
        $store = ProjectHelper::getStoreByRequest($request);
        $product = Product::findById($productId);
        $productCategory = ProductCategory::findById($productCategoryId);

        $categoriesToCheck = collect([]);
        $orderLimits = [];

        if ($product) {
            $orderLimit = $product->getPerOrderLimit([
                'store' => $store->id,
                'date' => $date,
            ]);

            $categoriesToCheck = $categoriesToCheck->merge($product->categories);

            if ($orderLimit) $orderLimits[] = $orderLimit;
        }

        if ($productCategory) {
            $categoriesToCheck = $categoriesToCheck->push($productCategory);
        }

        $categoriesToCheck = $categoriesToCheck->unique('id');

        foreach ($categoriesToCheck as $productCategory) {
            $orderLimit = $productCategory->getPerOrderLimit([
                'store' => $store->id,
                'date' => $date,
            ]);

            if ($orderLimit) $orderLimits[] = $orderLimit;
        }

        $leastOrderLimit = null;

        foreach ($orderLimits as $orderLimit) {
            if (!$leastOrderLimit || $orderLimit['limit'] < $leastOrderLimit['limit']) $leastOrderLimit = $orderLimit;
        }

        $response = new OrderLimitResource($leastOrderLimit['object'] ?? null);

        return $response->response();
    }

    /**
     * @param Request $request
     * @param Order $order
     * @return Order
     * @throws \Throwable
     */
    protected function placeOrder(Request $request, Order $order) {
        if (!empty($order->status) && !in_array($order->status, [Order::STATUS_CART, Order::STATUS_ADMIN_CART])) {
            throw new \Exception('Order status is not in cart.');
        }

        $order->status = Order::STATUS_PENDING;
        $order->checkout_at = Carbon::now();
        $order->ip_address = $request->ip();
        $order->user_agent = $request->header('User-Agent');
        $order->generateReference();

        Event::fire(new OrderEvent('before_order_placed', $order));

        $profileData = $order->billingInformation->getDetails();
        $customer = Customer::saveCustomer(
            $order->customer,
            $profileData,
            null,
            FALSE
        );

        if ($customer) {
            $order->customer()->associate($customer);
        }

        foreach($order->getCouponLineItems() as $couponLineItem){
            if($couponLineItem->coupon){
                Event::fire(new CouponEvent('used', $couponLineItem->coupon));
            }
        }

        OrderHelper::processLineItems($request, $order, false);

        Event::fire(new OrderEvent('before_checkout_calculate_total', $order));

        $order->load('lineItems');
        $order->calculateTotal();

        return $order;
    }

    /**
     * @param Request $request
     * @param Order $order
     * @return Order
     */
    protected function processPlaceOrder(Request $request, Order $order) {
        $order->processStocks();

        $order->saveData(['checkout_step' => 'complete'], TRUE);
        Event::fire(new OrderEvent('before_update_order', $order));

        $order->save();

        Event::fire(new OrderEvent('customer_place_order', $order));
        Event::fire(new OrderUpdate($order, Order::STATUS_CART, true));

        return $order;
    }

    /**
     * @param Order $order
     * @param PaymentMethod $paymentMethod
     * @param Request $request
     * @return Payment|array
     */
    protected function processFinalPayment(Order $order, PaymentMethod $paymentMethod, Request $request) {
        return $paymentMethod->getProcessor()->finalProcessPayment([
            'order' => $order,
            'request' => $request
        ]);
    }

    /**
     * @param array $profileData
     * @return bool
     */
    protected function isProfileOverrideable(array $profileData) {
        $addressFields = AddressHelper::getAddressFields();

        $bools = array_map(function($field) use ($profileData) {
            return empty($profileData[$field]);
        }, $addressFields);

        return count(array_unique($bools)) === 1;
    }
}
