<?php

namespace Kommercio\Http\Controllers\Frontend;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                    'products.*.id' => 'required|exists:products,id,deleted_at,NULL|is_available|is_active|is_purchaseable',
                    'products.*.quantity' => 'required|integer|min:0'
                ];

                foreach($request->input('products', []) as $idx => $productLineItem){
                    $rules['products.'.$idx.'.id'] = 'is_in_stock:'.$productLineItem['quantity'];
                }

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
            'product_id' => 'required|exists:products,id,deleted_at,NULL|is_available|is_active|is_in_stock:'.$request->input('quantity').'|is_purchaseable',
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

        $paymentMethodOptions = $this->getPaymentMethodOptions($request);

        $shippingMethodOptions = $this->getShippingMethodOptions($request, $order);

        $oldValues = old();

        if(!$oldValues){
            $oldValues['billingProfile'] = $order->billingProfile?$order->billingProfile->getDetails():[];
            $oldValues['shippingProfile'] = $order->shippingProfile?$order->shippingProfile->getDetails():[];
            $oldValues['shipping_method'] = $order->getSelectedShippingMethod();
            $oldValues['payment_method'] = $order->payment_method_id;
            $oldValues['delivery_date'] = $order->delivery_date?$order->delivery_date->format('Y-m-d'):null;
            $oldValues['additional_fields'] = $order->additional_fields;

            Session::flashInput($oldValues);
        }

        $addressOptions = $this->getAddressOptions($request, $order);

        return view($view_name, [
            'order' => $order,
        ] + $addressOptions + $shippingMethodOptions + $paymentMethodOptions);
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

        $order->saveProfile('billing', $request->input('billingProfile'));
        $order->saveProfile('shipping', $request->input('shippingProfile'));

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
                'billingProfile.email' => 'required|email',
                'billingProfile.full_name' => 'required',
                'billingProfile.phone_number' => 'required',
                'billingProfile.address_1' => 'required',
                'shippingProfile.email' => 'required|email',
                'shippingProfile.full_name' => 'required',
                'shippingProfile.phone_number' => 'required',
                'shippingProfile.address_1' => 'required',
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

            $profileData = $request->input('billingProfile');

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
                $renderData = view(ProjectHelper::getViewTemplate('frontend.order.checkout_summary'), ['order' => $order])->render();

                return new JsonResponse([
                    'data' => $renderData,
                    '_token' => csrf_token()
                ]);
            }
        }

        return redirect()
            ->back()
            ->with('success', [isset($message)?$message:'']);
    }

    public function onePageCheckout(Request $request)
    {
        $order = FrontendHelper::getCurrentOrder();

        if($order->itemsCount <= 0){
            return redirect(FrontendHelper::get_url('cart'))->withErrors([trans(LanguageHelper::getTranslationKey('frontend.checkout.empty_order'))]);
        }

        $view_name = ProjectHelper::getViewTemplate('frontend.order.checkout');

        $customer = $order->billingInformation?Customer::getByEmail($order->billingInformation->email):null;
        $canLogin = FALSE;
        $customerLoggedIn = FALSE;

        $step = $order->getData('checkout_step', 'account');

        if($customer){
            if($customer->user && Auth::check() && Auth::user()->id == $customer->user->id){
                $customerLoggedIn = TRUE;
            }elseif($customer->user){
                $canLogin = TRUE;
            }
        }

        $paymentMethodOptions = $this->getPaymentMethodOptions($request);

        $shippingMethodOptions = $this->getShippingMethodOptions($request, $order);

        $oldValues = old();

        if(!$oldValues){
            $oldValues['billingProfile'] = $order->billingProfile?$order->billingProfile->getDetails():[];
            $oldValues['shippingProfile'] = $order->shippingProfile?$order->shippingProfile->getDetails():[];
            $oldValues['shipping_method'] = $order->getSelectedShippingMethod();
            $oldValues['payment_method'] = $order->payment_method_id;
            $oldValues['delivery_date'] = $order->delivery_date?$order->delivery_date->format('Y-m-d'):null;
            $oldValues['additional_fields'] = $order->additional_fields;

            Session::flashInput($oldValues);
        }

        $addressOptions = $this->getAddressOptions($request, $order);

        return view($view_name, [
            'order' => $order,
            'step' => $step,
            'customer' => $customer,
            'customerLoggedIn' => $customerLoggedIn,
            'canLogin' => $canLogin,
        ] + $addressOptions + $shippingMethodOptions + $paymentMethodOptions);
    }

    public function onePageCheckoutProcess(Request $request, $type = 'account')
    {
        $order = FrontendHelper::getCurrentOrder();

        $errorCode = 400;
        $errors = [];
        $renderData = null;

        Event::fire(new OrderEvent('before_onepage_checkout_process', $order, ['request' => $request]));

        $viewData = [
            'order' => $order,
            'customer' => Customer::getByEmail($request->input('billingProfile.email')),
            'canLogin' => FALSE,
            'customerLoggedIn' => FALSE,
            'step' => $order->getData('checkout_step', 'account'),
            'previous_step' => $order->getData('checkout_step', 'account'),
            'success' => []
        ];

        $process = $request->input('process');

        //Save customer to order
        if($viewData['customer']){
            if($viewData['customer']->user && Auth::check() && Auth::user()->id == $viewData['customer']->user->id){
                $viewData['customerLoggedIn'] = TRUE;
            }elseif($viewData['customer']->user){
                $viewData['canLogin'] = TRUE;
            }
        }

        switch($type){
            case 'account':
                if($process == 'change'){
                    $viewData['canLogin'] = false;
                    $viewData['customerLoggedIn'] = false;

                    if($viewData['previous_step'] == 'account'){
                        //Reset email
                        $order->saveProfile('billing', ['email' => null]);
                    }

                    $renderData = [
                        'account' => ProjectHelper::getViewTemplate('frontend.order.one_page.account'),
                    ];

                    $viewData['step'] = 'account';
                }elseif($process == 'login'){
                    $this->validate($request, $this->getCheckoutRuleBook('login'));

                    $renderData = [
                        'account' => ProjectHelper::getViewTemplate('frontend.order.one_page.account'),
                    ];

                    if (Auth::attempt(['email' => $request->input('billingProfile.email'), 'password' => $request->input('password')])) {
                        $viewData['step'] = 'customer_information';
                    }else{
                        $viewData['step'] = 'account';
                        $errors['password'] = [trans(LanguageHelper::getTranslationKey('frontend.login.invalid_password'))];
                        $errorCode = 401;
                    }
                }elseif($process == 'continue_as_guest'){
                    $this->validate($request, $this->getCheckoutRuleBook('continue_as_guest'));

                    $viewData['step'] = 'customer_information';

                    $addressOptions = $this->getAddressOptions($request, $order);

                    $viewData += $addressOptions;

                    $renderData = [
                        'customer_information' => ProjectHelper::getViewTemplate('frontend.order.one_page.customer_information')
                    ];
                }else{
                    $this->validate($request, $this->getCheckoutRuleBook('account'));

                    //Save customer to order
                    if($viewData['customer']){
                        $order->customer()->associate($viewData['customer']);
                    }

                    //Save email to order
                    $order->saveProfile('billing', ['email' => $request->input('billingProfile.email')]);

                    $viewData['step'] = 'account';

                    $renderData = [
                        'account' => ProjectHelper::getViewTemplate('frontend.order.one_page.account'),
                    ];
                }

                break;
            case 'customer_information':
                if($process == 'change'){
                    $viewData['step'] = 'customer_information';

                    $addressOptions = $this->getAddressOptions($request, $order);

                    $viewData += $addressOptions;

                    $renderData = [
                        'customer_information' => ProjectHelper::getViewTemplate('frontend.order.one_page.customer_information')
                    ];
                }else{
                    $this->validate($request, $this->getCheckoutRuleBook('customer_information'));

                    $viewData['step'] = 'payment_method';

                    $order->saveProfile('shipping', $request->input('shippingProfile'));
                    $order->store()->associate(ProjectHelper::getStoreByRequest($request));

                    $paymentMethodOptions = $this->getPaymentMethodOptions($request);

                    $viewData += $paymentMethodOptions;

                    $renderData = [
                        'payment_method' => ProjectHelper::getViewTemplate('frontend.order.one_page.payment_method')
                    ];
                }

                break;
            case 'payment_method':
                if($process == 'change'){
                    $viewData['step'] = 'payment_method';

                    $paymentMethodOptions = $this->getPaymentMethodOptions($request);

                    $viewData += $paymentMethodOptions;

                    $renderData = [
                        'payment_method' => ProjectHelper::getViewTemplate('frontend.order.one_page.payment_method')
                    ];
                }else{
                    $this->validate($request, $this->getCheckoutRuleBook('payment_method'));

                    $viewData['step'] = 'checkout_summary';

                    $shippingMethodOptions = $this->getShippingMethodOptions($request, $order);

                    $viewData += $shippingMethodOptions;

                    $renderData = [
                        'checkout_summary' => ProjectHelper::getViewTemplate('frontend.order.one_page.checkout_summary')
                    ];
                }
                break;
            case 'checkout_summary':
                if($process == 'add_coupon'){
                    $rules = [
                        'coupon_code' => 'required'
                    ];

                    $this->validate($request, $rules);

                    $couponCode = $request->input('coupon_code', 'ERRORCOUPON');

                    $couponPriceRules = CartPriceRule::addCoupon($couponCode, $request, $order);

                    //If above method returns string, it is returning error message
                    if(is_string($couponPriceRules)){
                        $errors['coupon_code'] = [$couponPriceRules];
                    }else{
                        $coupon = CartPriceRule::getCouponByCode($couponCode);

                        $viewData['order']->addCoupon($coupon);
                        $viewData['success'][] = trans(LanguageHelper::getTranslationKey('frontend.order.coupon_added'));
                    }
                }elseif(strpos($process, 'remove_coupon_') !== false){
                    $couponId = str_replace('remove_coupon_', '', $process);
                    $coupon = CartPriceRule::findOrFail($couponId);
                    $order->removeCoupon($coupon);

                    $viewData['success'][] = trans(LanguageHelper::getTranslationKey('frontend.order.coupon_removed'));
                }

                //Process shipping
                if($request->has('shipping_method')){
                    $shippingMethodOptions = $this->getShippingMethodOptions($request, $order);

                    $rules = [
                        'shipping_method' => 'in:'.implode(',', array_keys($shippingMethodOptions['shippingMethodOptions']))
                    ];

                    $this->validate($request, $rules);

                    $order->updateShippingMethod($request->input('shipping_method'));
                }

                OrderHelper::processLineItems($request, $viewData['order'], false);

                $viewData['order']->load('lineItems');
                $viewData['order']->calculateTotal();

                $renderData = [
                    'order_table' => ProjectHelper::getViewTemplate('frontend.order.one_page.order_table')
                ];
                break;
            default:
                return redirect()->back()->withErrors(['What?']);
                break;
        }

        if($viewData['previous_step'] != $viewData['step']){
            $renderData[$viewData['previous_step']] = ProjectHelper::getViewTemplate('frontend.order.one_page.'.$viewData['previous_step']);
        }

        if(!$errors){
            if($request->has('additional_fields')){
                $order->additional_fields = $request->input('additional_fields');
            }

            $order->saveData(['checkout_step' => $viewData['step']]);
            $order->save();

            $order->billingProfile->fillDetails();
            $order->shippingProfile->fillDetails();
        }


        if($request->ajax()){
            //Remove old input, because if we return AJAX, old input is still kept

            if($errors){
                $response = new JsonResponse($errors, $errorCode);
            }else{
                foreach($renderData as &$renderDatum){
                    $renderDatum = view($renderDatum, $viewData)->render();
                }

                $response = new JsonResponse([
                    'data' => $renderData,
                    'step' => $viewData['step'],
                    '_token' => csrf_token()
                ]);
            }
        }else{
            $response = redirect()->back();

            if($errors){
                $response->withErrors($errors);
            }
        }

        return $response;

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

        $order->saveProfile('billing', $request->input('billingProfile'));
        $order->saveProfile('shipping', $request->input('shippingProfile'));

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
                'billingProfile.email' => 'required|email',
                'billingProfile.full_name' => 'required',
                'billingProfile.phone_number' => 'required',
                'billingProfile.address_1' => 'required',
                'shippingProfile.email' => 'required|email',
                'shippingProfile.full_name' => 'required',
                'shippingProfile.phone_number' => 'required',
                'shippingProfile.address_1' => 'required',
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

            $profileData = $request->input('billingProfile');

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
                switch($request->input('order_update_type')){
                    case 'account':
                        break;
                    case 'billing_information':
                        break;
                    case 'shipping_information':
                        break;
                    case 'payment_method':
                        break;
                    case 'shipping_method':
                        break;
                    default:
                        $renderData = view(ProjectHelper::getViewTemplate('frontend.order.checkout_summary'), ['order' => $order])->render();
                        break;
                }

                return new JsonResponse([
                    'data' => $renderData,
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

    protected function getAddressOptions(Request $request, $order)
    {
        $profileCountryOptions = ['' => trans(LanguageHelper::getTranslationKey('order.address.select_country'))] + AddressHelper::getCountryOptions();
        $profileStateOptions = ['' => trans(LanguageHelper::getTranslationKey('order.address.select_state'))] + AddressHelper::getStateOptions($request->old('billingProfile.country_id', count($profileCountryOptions) < 3?key(array_slice($profileCountryOptions, 1, 1, true)):$order->billingInformation->country_id));
        $profileCityOptions = ['' => trans(LanguageHelper::getTranslationKey('order.address.select_city'))] + AddressHelper::getCityOptions($request->old('billingProfile.state_id', $order->billingInformation->state_id));
        $profileDistrictOptions = ['' => trans(LanguageHelper::getTranslationKey('order.address.select_district'))] + AddressHelper::getDistrictOptions($request->old('billingProfile.city_id', $order->billingInformation->city_id));
        $profileAreaOptions = ['' => trans(LanguageHelper::getTranslationKey('order.address.select_area'))] + AddressHelper::getAreaOptions($request->old('billingProfile.district_id', $order->billingInformation->district_id));

        $shippingCountryOptions = ['' => trans(LanguageHelper::getTranslationKey('order.address.select_country'))] + AddressHelper::getCountryOptions();
        $shippingStateOptions = ['' => trans(LanguageHelper::getTranslationKey('order.address.select_state'))] + AddressHelper::getStateOptions($request->old('shippingProfile.country_id', count($shippingCountryOptions) < 3?key(array_slice($shippingCountryOptions, 1, 1, true)):$order->shippingInformation->country_id));
        $shippingCityOptions = ['' => trans(LanguageHelper::getTranslationKey('order.address.select_city'))] + AddressHelper::getCityOptions($request->old('shippingProfile.state_id', $order->shippingInformation->state_id));
        $shippingDistrictOptions = ['' => trans(LanguageHelper::getTranslationKey('order.address.select_district'))] + AddressHelper::getDistrictOptions($request->old('shippingProfile.city_id', $order->shippingInformation->city_id));
        $shippingAreaOptions = ['' => trans(LanguageHelper::getTranslationKey('order.address.select_area'))] + AddressHelper::getAreaOptions($request->old('shippingProfile.district_id', $order->shippingInformation->district_id));

        return [
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
        ];
    }

    protected function getShippingMethodOptions(Request $request, $order)
    {
        $shippingMethodOptions = ShippingMethod::getShippingMethods([
            'order' => $order,
            'request' => $request
        ]);

        return [
            'shippingMethodOptions' => $shippingMethodOptions,
        ];
    }

    protected function getPaymentMethodOptions(Request $request)
    {
        $paymentMethods = PaymentMethod::getPaymentMethods();

        $paymentMethodOptions = [];
        foreach($paymentMethods as $paymentMethod){
            $paymentMethodOptions[$paymentMethod->id] = $paymentMethod->name;
        }

        return [
            'paymentMethods' => $paymentMethods,
            'paymentMethodOptions' => $paymentMethodOptions,
        ];
    }

    protected function getCheckoutRuleBook($type)
    {
        $ruleBook = [
            'login' => [
                'billingProfile.email' => 'required|email',
                'password' => 'required'
            ],
            'account' => [
                'billingProfile.email' => 'required|email'
            ],
            'continue_as_guest' => [
                'billingProfile.email' => 'required|email'
            ],
            'customer_information' => [
                'shippingProfile.full_name' => 'required',
                'shippingProfile.phone_number' => 'required',
                'shippingProfile.country_id' => 'required',
                'shippingProfile.state_id' => 'descendant_address:state',
                'shippingProfile.city_id' => 'descendant_address:city',
                'shippingProfile.district_id' => 'descendant_address:district',
                'shippingProfile.area_id' => 'descendant_address:area',
                'shippingProfile.address_1' => 'required',
            ],
            'payment_method' => [
                'payment_method' => 'required|exists:payment_methods,id'
            ]
        ];

        if(ProjectHelper::getConfig('require_billing_information')){
            $ruleBook['customer_information'] += [
                'billingProfile.full_name' => 'required',
                'billingProfile.phone_number' => 'required',
                'billingProfile.address_1' => 'required',
                'shippingProfile.email' => 'required|email',
            ];
        }

        return $ruleBook[$type];
    }
}