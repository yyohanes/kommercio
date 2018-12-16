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
use Kommercio\Http\Resources\Order\DayAvailabilityResource;
use Kommercio\Http\Resources\Order\DisabledDateCollection;
use Kommercio\Http\Resources\Order\OrderLimitResource;
use Kommercio\Http\Resources\Order\OrderResource;
use Kommercio\Http\Resources\PaymentMethod\PublicPaymentMethodResource;
use Kommercio\Http\Resources\ShippingMethod\ShippingOptionCollection;
use Kommercio\Models\Address\Area;
use Kommercio\Models\Address\City;
use Kommercio\Models\Address\Country;
use Kommercio\Models\Address\District;
use Kommercio\Models\Address\State;
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
     * Get orders
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) {
        $user = $request->user('api');

        $qb = Order::checkout()->orderBy('checkout_at', 'DESC');

        // TODO: Cache this per customer
        $customer = $user->customer;
        if ($customer) {
            $qb->where('customer_id', $customer->id);
        }

        $orders = $qb->get();

        $resources = OrderResource::collection($orders);

        return $resources->response();
    }

    /**
     * Get order by reference
     * @param string $reference
     * @param Request $request
     * @return JsonResponse
     */
    public function getByReference(Request $request, $reference) {
        $user = $request->user('api');

        $qb = Order::where('reference', $reference)
            ->checkout();

        $customer = $user->customer;
        if ($customer) {
            $qb->where('customer_id', $customer->id);
        }

        $order = $qb->firstOrFail();

        $resources = new OrderResource($order);

        return $resources->response();
    }

    /**
     * Get order by public id
     * @param string $publicId
     * @param Request $request
     * @return JsonResponse
     */
    public function getByPublicId(Request $request, $publicId) {
        $user = $request->user('api');

        $qb = Order::where('public_id', $publicId)
            ->checkout();

        $customer = $user->customer;
        if ($customer) {
            $qb->where('customer_id', $customer->id);
        }

        $order = $qb->firstOrFail();

        $resources = new OrderResource($order);

        return $resources->response();
    }

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

        $country = Country::findById($shippingProfileDetails['country_id']);
        $shippingMethod = ShippingMethod::findById($request->get('shipping_method'));

        // Process remote place if country is using remote city
        if ($request->filled('shippingProfile.remote_place') && $country->use_remote_city) {
            $remotePlaces = $this->processRemotePlace($country, $request->input('shippingProfile.remote_place'));

            // Override addresses with remote data
            if ($remotePlaces['state']) {
                $shippingProfileDetails['state_id'] = $remotePlaces['state']->id;
            }

            if ($remotePlaces['city']) {
                $shippingProfileDetails['city_id'] = $remotePlaces['city']->id;
            }

            if ($remotePlaces['district']) {
                $shippingProfileDetails['district_id'] = $remotePlaces['district']->id;
            }

            if ($remotePlaces['area']) {
                $shippingProfileDetails['area_id'] = $remotePlaces['area']->id;
            }
        } else if ($shippingMethod && !$shippingMethod->requireAddress) {
            // Address is required for an order. But some shipping method doesn't require them.
            // Thus, get it from store addresses
            $storeAddress = $store->getAddress();
            $shippingProfileDetails['state_id'] = $shippingProfileDetails['state_id'] ?? ($storeAddress['state_id'] ?? null);
            $shippingProfileDetails['city_id'] = $shippingProfileDetails['city_id'] ?? ($storeAddress['city_id'] ?? null);
            $shippingProfileDetails['custom_city'] = $shippingProfileDetails['custom_city'] ?? ($storeAddress['custom_city'] ?? null);
            $shippingProfileDetails['district_id'] = $shippingProfileDetails['district_id'] ?? ($storeAddress['district_id'] ?? null);
            $shippingProfileDetails['area_id'] = $shippingProfileDetails['area_id'] ?? ($storeAddress['area_id'] ?? null);
        }

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
            $this->beforePlaceOrder($request, $order);

            // Final process payment
            $paymentResult = $this->processFinalPayment(
                $order,
                $paymentMethod,
                $request
            );
        } catch (\Throwable $e) {
            report($e);
        }

        if (is_array($paymentResult)) {
            return new JsonResponse(
                [
                    'errors' => $paymentResult,
                ],
                422
            );
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
            'shipping_method' => [
                'numeric',
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

        // Give shipping method a chance to filter dates
        if ($request->filled('shipping_method')) {
            $shippingMethod = ShippingMethod::findById($request->get('shipping_method'));

            if ($shippingMethod) {
                try {
                    foreach ($dates as $idx => $date) {
                        $carbonDate = Carbon::createFromFormat('Y-m-d', $date);
                        if (!$shippingMethod->getProcessor()->validateDateAvailability($carbonDate)) {
                            $disabledDates[] = $date;
                            unset($dates[$idx]);
                        }
                    }
                } catch (\Throwable $e) {
                    // no-op
                }
            }
        }

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
     * Get available times of given dates
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function daysAvailability(Request $request) {
        $rules = [
            'products' => [
                'nullable',
                'array',
            ],
            'products.*' => [
                'nullable',
                'integer',
            ],
            'dates' => [
                'required',
                'string',
            ],
            'shippingProfile' => [
                'nullable',
                'array',
            ],
            'shipping_method_id' => [
                'required',
                'integer',
            ],
        ];

        $this->validate($request, $rules);

        $dates = explode(',', $request->get('dates', ''));

        $products = [];
        $orderedQuantities = [];

        foreach ($request->get('products', []) as $productId => $orderedQuantity) {
            $product = Product::findById($productId);

            if (!$product) continue;

            $products[$productId] = $product;
            $orderedQuantities[$productId] = $orderedQuantity;
        }

        $store = ProjectHelper::getStoreByRequest($request);

        $options = [
            'store' => $store,
            'shippingProfile' => $request->input('shippingProfile'),
        ];

        $shippingMethod = ShippingMethod::findById($request->input('shipping_method_id'));

        $returnedDates = [];
        foreach ($dates as $date) {
            // We rely on shipping method to give us available times
            // TODO: Add availability-by-time feature?
            try {
                $returnedDates = array_merge(
                    $returnedDates,
                    $shippingMethod
                        ->getProcessor()
                        ->getDayAvailability(
                            Carbon::createFromFormat('Y-m-d', $date),
                            $options
                        )
                );
            } catch (\Exception $e) {
                report($e);
            }
        }

        $response = new DayAvailabilityResource($returnedDates);

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
    protected function beforePlaceOrder(Request $request, Order $order) {
        if (!empty($order->status) && !in_array($order->status, [Order::STATUS_CART, Order::STATUS_ADMIN_CART])) {
            throw new \Exception('Order status is not in cart.');
        }

        $profileData = $order->billingInformation->getDetails();

        $shippingMethod = ShippingMethod::findById($request->input('shipping_method'));
        // Address is required for an order. But some shipping method doesn't require them.
        // In such case, we fill it with store address NOT customer address.
        // To prevent misleading customer, customer should not save this address.
        if ($shippingMethod && !$shippingMethod->requireAddress) {
            $addressFields = AddressHelper::getAddressFields();
            foreach ($addressFields as $addressField) {
                if ($profileData[$addressField]) {
                    unset($profileData[$addressField]);
                }
            }
        }

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

        Event::fire(new OrderEvent('before_order_placed', $order));

        return $order;
    }

    /**
     * @param Request $request
     * @param Order $order
     * @return Order
     */
    protected function processPlaceOrder(Request $request, Order $order) {
        $order->status = Order::STATUS_PENDING;
        $order->checkout_at = Carbon::now();

        // Allow overriding IP address and user-agent as this API might be behind proxy
        $order->ip_address = $request->input('ip_address', $request->ip());
        $order->user_agent = $request->input('user_agent', $request->header('User-Agent'));

        $order->generateReference();

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

    /**
     * @param array $remotePlaceData
     * @return array
     */
    protected function processRemotePlace(Country $country, array $remotePlaceData) {
        $remoteSource = $remotePlaceData['source'];

        $holder = [
            'country' => $country,
            'state' => null,
            'city' => null,
            'district' => null,
            'area' => null,
        ];
        $components = $remotePlaceData['components'];

        $map = [
            'country' => 0,
            'state' => 1,
            'city' => 2,
            'district' => 3,
            'area' => 4,
        ];

        $components = array_sort($components, function($component) use ($map) {
            return $map[$component['local_type']] ?? -1;
        });

        foreach ($components as $component) {
            switch ($component['local_type']) {
                case 'state':
                    $state = $holder['country']
                        ->states()
                        ->where('name', $component['name'])
                        ->first();

                    if (!$state) {
                        $state = new State([
                            'name' => $component['name'],
                            'active' => true,
                            'remote_type' => $component['type'],
                            'remote_source' => $remoteSource,
                            'has_descendant' => count(array_filter($components, function($component) {
                                return $component['local_type'] === 'city';
                            })) > 0,
                        ]);
                        $state->setParent($holder['country']->id);
                        $state->save();
                    }

                    $holder['state'] = $state;
                    break;
                case 'city':
                    $city = $holder['state']
                        ->cities()
                        ->where('name', $component['name'])
                        ->first();

                    if (!$city) {
                        $city = new City([
                            'name' => $component['name'],
                            'active' => true,
                            'remote_type' => $component['type'],
                            'remote_source' => $remoteSource,
                            'has_descendant' => count(array_filter($components, function($component) {
                                return $component['local_type'] === 'district';
                            })) > 0,
                        ]);
                        $city->setParent($holder['state']->id);
                        $city->save();
                    }

                    $holder['city'] = $city;
                    break;
                case 'district':
                    $district = $holder['city']
                        ->districts()
                        ->where('name', $component['name'])
                        ->first();

                    if (!$district) {
                        $district = new District([
                            'name' => $component['name'],
                            'active' => true,
                            'remote_type' => $component['type'],
                            'remote_source' => $remoteSource,
                            'has_descendant' => count(array_filter($components, function($component) {
                                return $component['local_type'] === 'area';
                            })) > 0,
                        ]);
                        $district->setParent($holder['city']->id);
                        $district->save();
                    }

                    $holder['district'] = $district;
                    break;
                case 'area':
                    $area = $holder['district']
                        ->areas()
                        ->where('name', $component['name'])
                        ->first();

                    if (!$area) {
                        $area = new Area([
                            'name' => $component['name'],
                            'active' => true,
                            'remote_type' => $component['type'],
                            'remote_source' => $remoteSource,
                        ]);
                        $area->setParent($holder['district']->id);
                        $area->save();
                    }

                    $holder['area'] = $area;
                    break;
            }
        }

        return $holder;
    }
}
