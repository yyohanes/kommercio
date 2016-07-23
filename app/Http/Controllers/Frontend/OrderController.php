<?php

namespace Kommercio\Http\Controllers\Frontend;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Session;
use Kommercio\Events\OrderEvent;
use Kommercio\Events\OrderUpdate;
use Kommercio\Facades\AddressHelper;
use Kommercio\Facades\FrontendHelper;
use Kommercio\Facades\LanguageHelper;
use Kommercio\Facades\OrderHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Customer;
use Kommercio\Models\Order\Order;
use Kommercio\Models\PaymentMethod\PaymentMethod;
use Kommercio\Models\PriceRule\CartPriceRule;
use Kommercio\Models\Product;
use Kommercio\Models\ShippingMethod\ShippingMethod;
use Symfony\Component\HttpFoundation\JsonResponse;

class OrderController extends Controller
{
    public function cart(Request $request)
    {
        $view_name = ProjectHelper::getViewTemplate('frontend.order.cart');
        $productLineItems = FrontendHelper::getCurrentOrder()->getProductLineItems();

        return view($view_name, [
            'productLineItems' => $productLineItems
        ]);
    }

    public function cartUpdate(Request $request)
    {
        $order = FrontendHelper::getCurrentOrder();

        $immediateJump = false;

        if($request->has('product_remove')){
            $rules = [
                'product_remove' => 'required|exists:products,id'
            ];

            $this->validate($request, $rules);

            $product = Product::findOrFail($request->input('product_remove'));
            $order->removeFromCart($product);

            $immediateJump = true;

            $message = trans(LanguageHelper::getTranslationKey('frontend.order.removed_from_cart'), ['product' => $product->name]);
        }

        if(!$immediateJump){
            if($request->input('update_cart', 0) == 1){
                $rules = [
                    'products.*.id' => 'required|exists:products',
                    'products.*.quantity' => 'required|integer|min:0'
                ];

                $this->validate($request, $rules);

                foreach($request->input('products', []) as $idx => $productLineItem){
                    $product = Product::findOrFail($productLineItem['id']);
                    $order->updateQuantity($product, $productLineItem['quantity']);
                }

                $message = trans(LanguageHelper::getTranslationKey('frontend.order.updated_cart'));
            }elseif($request->input('add_coupon', 0) == 1){
                $rules = [
                    'coupon_code' => 'required'
                ];

                $this->validate($request, $rules);

                $couponCode = $request->input('coupon_code', 'ERRORCOUPON');

                $couponPriceRules = CartPriceRule::addCoupon($couponCode, $request, $order);

                //If above method returns string, it is returning error message
                if(is_string($couponPriceRules)){
                    return redirect()->back()->withErrors([
                        'coupon_code' => [$couponPriceRules]
                    ]);
                }

                $coupon = CartPriceRule::getCouponByCode($couponCode);
                $order->addCoupon($coupon);

                $message = trans(LanguageHelper::getTranslationKey('frontend.order.coupon_added'));
            }elseif($request->has('coupon_remove')){
                $rules = [
                    'coupon_remove' => 'required|exists:cart_price_rules,id'
                ];

                $this->validate($request, $rules);

                $coupon = CartPriceRule::findOrFail($request->input('coupon_remove'));
                $order->removeCoupon($coupon);

                $message = trans(LanguageHelper::getTranslationKey('frontend.order.coupon_removed'));
            }

            OrderHelper::processLineItems($request, $order, false);

            $order->load('lineItems');
            $order->calculateTotal();
            $order->save();
        }

        return redirect()
            ->back()
            ->with('success', [$message]);
    }

    public function addToCart(Request $request)
    {
        $rules = [
            'product_id' => 'required|exists:products,id,deleted_at,NULL|is_available|is_active|is_in_stock|is_purchaseable',
            'quantity' => 'required|integer|min:0'
        ];

        $this->validate($request, $rules);

        $product_id = $request->input('product_id');

        $product = Product::findOrFail($product_id);

        $order = FrontendHelper::getCurrentOrder('save');

        $order->addToCart($product, $request->input('quantity'));

        if($request->ajax()){
            return new JsonResponse([
                'data' => [
                    'itemsCount' => $order->itemsCount
                ],
                'success' => [trans(LanguageHelper::getTranslationKey('frontend.order.added_to_cart'), ['product' => $product->name])],
                '_token' => csrf_token()
            ]);
        }else{
            return redirect()
                ->back()
                ->with('success', [trans(LanguageHelper::getTranslationKey('frontend.order.added_to_cart'), ['product' => $product->name])]);
        }
    }

    public function checkout(Request $request)
    {
        $order = FrontendHelper::getCurrentOrder();

        if($order->itemsCount <= 0){
            return redirect(FrontendHelper::get_url('cart'))->withErrors([trans(LanguageHelper::getTranslationKey('frontend.checkout.empty_order'))]);
        }

        $view_name = ProjectHelper::getViewTemplate('frontend.order.checkout');

        $shippingMethods = ShippingMethod::getAvailableMethods();
        $shippingMethodOptions = [];

        $paymentMethods = PaymentMethod::getPaymentMethods();

        $paymentMethodOptions = [];
        foreach($paymentMethods as $paymentMethod){
            $paymentMethodOptions[$paymentMethod->id] = $paymentMethod->name;
        }

        foreach($shippingMethods as $idx => $shippingMethod){
            $shippingMethodOptions[$idx] = $shippingMethod['name'];
        }

        $profileCountryOptions = AddressHelper::getCountryOptions();
        $profileStateOptions = AddressHelper::getStateOptions($request->old('profile.country_id', count($profileCountryOptions) < 2?key($profileCountryOptions):null));
        $profileCityOptions = AddressHelper::getCityOptions($request->old('profile.state_id'));
        $profileDistrictOptions = AddressHelper::getDistrictOptions($request->old('profile.city_id'));
        $profileAreaOptions = AddressHelper::getAreaOptions($request->old('profile.district_id'));

        $shippingCountryOptions = AddressHelper::getCountryOptions();
        $shippingStateOptions = AddressHelper::getStateOptions($request->old('shipping_profile.country_id', count($shippingCountryOptions) < 2?key($shippingCountryOptions):null));
        $shippingCityOptions = AddressHelper::getCityOptions($request->old('shipping_profile.state_id'));
        $shippingDistrictOptions = AddressHelper::getDistrictOptions($request->old('shipping_profile.city_id'));
        $shippingAreaOptions = AddressHelper::getAreaOptions($request->old('shipping_profile.district_id'));

        $oldValues = old();

        if(!$oldValues){
            $oldValues['profile'] = $order->billingProfile?$order->billingProfile->getDetails():[];
            $oldValues['shipping_profile'] = $order->shippingProfile?$order->shippingProfile->getDetails():[];
            $oldValues['shipping_method'] = $order->getSelectedShippingMethod();
            $oldValues['payment_method'] = $order->payment_method_id;
            $oldValues['delivery_date'] = $order->delivery_date?$order->delivery_date->format('Y-m-d'):null;
            $oldValues['additional_fields'] = $order->additional_fields;

            Session::flashInput($oldValues);
        }

        return view($view_name, [
            'order' => $order,
            'shippingMethodOptions' => $shippingMethodOptions,
            'paymentMethodOptions' => $paymentMethodOptions,
            'profileCountryOptions' => $profileCountryOptions,
            'profileStateOptions' => $profileStateOptions,
            'profileCityOptions' => $profileCityOptions,
            'profileDistrictOptions' => $profileDistrictOptions,
            'profileAreaOptions' => $profileAreaOptions,
            'shippingCountryOptions' => $shippingCountryOptions,
            'shippingStateOptions' => $shippingStateOptions,
            'shippingCityOptions' => $shippingCityOptions,
            'shippingDistrictOptions' => $shippingDistrictOptions,
            'shippingAreaOptions' => $shippingAreaOptions,
        ]);
    }

    public function checkoutProcess(Request $request)
    {
        $order = FrontendHelper::getCurrentOrder();

        $order->notes = $request->input('notes');
        $order->delivery_date = $request->input('delivery_date', null);
        $order->payment_method_id = $request->input('payment_method', null);
        $order->currency = $request->input('currency');
        $order->conversion_rate = 1;
        if($request->has('additional_fields')){
            $order->additional_fields = $request->input('additional_fields');
        }
        $order->store()->associate(ProjectHelper::getActiveStore());
        $order->save();

        $order->saveProfile('billing', $request->input('profile'));
        $order->saveProfile('shipping', $request->input('shipping_profile'));

        //Process shipping
        if($request->has('shipping_method')){
            $order->updateShippingMethod($request->input('shipping_method'));
        }

        OrderHelper::processLineItems($request, $order, FALSE);

        $order->load('lineItems');
        $order->calculateTotal();

        Event::fire(new OrderEvent('before_update_order', $order));

        if($request->has('place_order')){
            $rules = [
                'profile.email' => 'required|email',
                'profile.full_name' => 'required',
                'profile.phone_number' => 'required',
                'profile.address_1' => 'required',
                'shipping_profile.email' => 'required|email',
                'shipping_profile.full_name' => 'required',
                'shipping_profile.phone_number' => 'required',
                'shipping_profile.address_1' => 'required',
                'shipping_method' => 'required',
                'payment_method' => 'required'
            ];

            if(config('project.enable_delivery_date', FALSE)){
                $rules['delivery_date'] = 'required|date_format:Y-m-d';
            }

            $originalStatus = $order->status;

            Event::fire(new OrderEvent('built_frontend_rules', $order, ['rules' => &$rules]));

            $this->validate($request, $rules);

            $order->processStocks();

            $this->placeOrder($order);

            $profileData = $request->input('profile');

            $customer = Customer::saveCustomer($profileData);

            if($customer){
                $order->customer()->associate($customer);
            }

            $order->save();

            Event::fire(new OrderUpdate($order, $originalStatus, true));
            Event::fire(new OrderEvent('customer_place_order', $order));

            return redirect()
                ->route('frontend.order.checkout.complete')
                ->with('order_id', $order->id)
                ->with('success', [trans(LanguageHelper::getTranslationKey('frontend.checkout.checkout_complete'))]);
        }elseif($request->input('add_coupon', 0) == 1){
            $rules = [
                'coupon_code' => 'required'
            ];

            $this->validate($request, $rules);

            $couponCode = $request->input('coupon_code', 'ERRORCOUPON');

            $couponPriceRules = CartPriceRule::addCoupon($couponCode, $request, $order);

            //If above method returns string, it is returning error message
            if(is_string($couponPriceRules)){
                return redirect()->back()->withErrors([
                    'coupon_code' => [$couponPriceRules]
                ]);
            }

            $coupon = CartPriceRule::getCouponByCode($couponCode);
            $order->addCoupon($coupon);

            $message = trans(LanguageHelper::getTranslationKey('frontend.order.coupon_added'));
        }elseif($request->has('coupon_remove')){
            $rules = [
                'coupon_remove' => 'required|exists:cart_price_rules,id'
            ];

            $this->validate($request, $rules);

            $coupon = CartPriceRule::findOrFail($request->input('coupon_remove'));
            $order->removeCoupon($coupon);

            $message = trans(LanguageHelper::getTranslationKey('frontend.order.coupon_removed'));
        }

        OrderHelper::processLineItems($request, $order, false);

        $order->load('lineItems');
        $order->calculateTotal();
        $order->save();

        if($request->input('order_update', 0) == 1){
            if($request->ajax()){
                return new JsonResponse([
                    'data' => view(ProjectHelper::getViewTemplate('frontend.order.checkout_summary'), ['order' => $order])->render(),
                    '_token' => csrf_token()
                ]);
            }
        }

        return redirect()
            ->back()
            ->with('success', [isset($message)?$message:'']);
    }

    public function checkoutComplete(Request $request)
    {
        $order = Order::find($request->session()->get('order_id'));

        if(!$order || !$order->isCheckout){
            return redirect()->back()->withErrors([trans(LanguageHelper::getTranslationKey('frontend.order.order_not_complete'))]);
        }

        $view_name = ProjectHelper::getViewTemplate('frontend.order.complete');

        return view($view_name, ['order' => $order]);
    }

    protected function placeOrder(Order $order)
    {
        $order->status = Order::STATUS_PENDING;
        $order->checkout_at = Carbon::now();
        $order->ip_address = RequestFacade::ip();
        $order->user_agent = RequestFacade::header('User-Agent');
        $order->generateReference();

        Event::fire(new OrderEvent('before_place_order', $order));

        return $order;
    }
}