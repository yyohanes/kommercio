<?php

namespace Kommercio\Http\Controllers\Frontend;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Kommercio\Events\OrderEvent;
use Kommercio\Events\OrderUpdate;
use Kommercio\Facades\AddressHelper;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Facades\EmailHelper;
use Kommercio\Facades\FrontendHelper;
use Kommercio\Facades\LanguageHelper;
use Kommercio\Facades\NewsletterSubscriptionHelper;
use Kommercio\Facades\OrderHelper;
use Kommercio\Facades\PriceFormatter;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Customer;
use Kommercio\Models\File;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Order\Payment;
use Kommercio\Models\PaymentMethod\PaymentMethod;
use Kommercio\Models\PriceRule\CartPriceRule;
use Kommercio\Models\Product;
use Kommercio\Models\Profile\Profile;
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

    public function cartClear(Request $request)
    {
        $order = FrontendHelper::getCurrentOrder();

        $order->clearCart();

        $order->calculateTotal();
        $order->save();

        $message = trans(LanguageHelper::getTranslationKey('frontend.order.cart_clear'));

        if($request->ajax()){
            return new JsonResponse($message);
        }else{
            return redirect()
                ->back()
                ->with('success', [$message]);
        }
    }

    public function cartUpdate(Request $request)
    {
        $order = FrontendHelper::getCurrentOrder();

        if($request->has('product_remove')){
            $rules = [
                'product_remove' => 'required|exists:products,id'
            ];

            $this->validate($request, $rules);

            $product = Product::findOrFail($request->input('product_remove'));
            $order->removeFromCart($product);

            $message = trans(LanguageHelper::getTranslationKey('frontend.order.removed_from_cart'), ['product' => $product->name]);
        }

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
                'coupon_code' => 'required|valid_coupon:'.$order->id
            ];

            $this->validate($request, $rules);
            $coupon = CartPriceRule::getCouponByCode($request->input('coupon_code'));
            $order->addCoupon($coupon);

            $message = trans(LanguageHelper::getTranslationKey('frontend.order.coupon_added'), ['coupon_code' => $coupon->coupon_code]);
        }elseif($request->has('coupon_remove')){
            $rules = [
                'coupon_remove' => 'required|exists:cart_price_rules,id'
            ];

            $this->validate($request, $rules);

            $coupon = CartPriceRule::findOrFail($request->input('coupon_remove'));
            $order->removeCoupon($coupon);

            $message = trans(LanguageHelper::getTranslationKey('frontend.order.coupon_removed'), ['coupon_code' => $coupon->coupon_code]);
        }

        OrderHelper::processLineItems($request, $order, false);

        $order->load('lineItems');
        $order->calculateTotal();
        $order->save();

        return redirect()
            ->back()
            ->with('success', [$message]);
    }

    public function addToCart(Request $request)
    {
        if($request->has('products')){
            $rules = [
                'products.*.product_id' => 'required|exists:products,id,deleted_at,NULL|is_available|is_active|is_in_stock:'.$request->input('quantity').'|is_purchaseable',
                'products.*.quantity' => 'required|integer|min:0'
            ];

            $productData = $request->input('products');
        }else{
            $rules = [
                'product_id' => 'required|exists:products,id,deleted_at,NULL|is_available|is_active|is_in_stock:'.$request->input('quantity').'|is_purchaseable',
                'quantity' => 'required|integer|min:0'
            ];

            $productData = [
                ['product_id' => $request->input('product_id'), 'quantity' => $request->input('quantity')]
            ];
        }

        $this->validate($request, $rules);

        $messages = [];

        $order = FrontendHelper::getCurrentOrder('save');

        foreach($productData as $productDatum){
            $product_id = $productDatum['product_id'];

            $product = Product::findOrFail($product_id);

            $order->addToCart($product, $productDatum['quantity']);

            $messages[] = trans(LanguageHelper::getTranslationKey('frontend.order.added_to_cart'), ['product' => $product->name]);
        }

        OrderHelper::processLineItems($request, $order, false);
        $order->calculateTotal();
        $order->save();

        if($request->ajax()){
            return new JsonResponse([
                'data' => [
                    'itemsCount' => $order->itemsCount,
                    'total' => PriceFormatter::formatNumber($order->total)
                ],
                'success' => $messages,
                '_token' => csrf_token()
            ]);
        }else{
            return redirect()
                ->back()
                ->with('success', $messages);
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

            $customer = Customer::saveCustomer($profileData, null, false);

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
                'coupon_code' => 'required|valid_coupon:'.$order->id
            ];

            $this->validate($request, $rules);

            $coupon = CartPriceRule::getCouponByCode($request->input('coupon_code'));
            $order->addCoupon($coupon);

            $message = trans(LanguageHelper::getTranslationKey('frontend.order.coupon_added'), ['coupon_code' => $coupon->coupon_code]);
        }elseif($request->has('coupon_remove')){
            $rules = [
                'coupon_remove' => 'required|exists:cart_price_rules,id'
            ];

            $this->validate($request, $rules);

            $coupon = CartPriceRule::findOrFail($request->input('coupon_remove'));
            $order->removeCoupon($coupon);

            $message = trans(LanguageHelper::getTranslationKey('frontend.order.coupon_removed'), ['coupon_code' => $coupon->coupon_code]);
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
        $user = Auth::user();

        if($order->itemsCount <= 0){
            if($request->ajax()){
                return new JsonResponse([trans(LanguageHelper::getTranslationKey('frontend.checkout.empty_order'))], 422);
            }else{
                return redirect(FrontendHelper::get_url('cart'))->withErrors([trans(LanguageHelper::getTranslationKey('frontend.checkout.empty_order'))]);
            }
        }

        $view_name = ProjectHelper::getViewTemplate('frontend.order.checkout');

        //If logged in, update owner to new user and skip Account step
        if($user){
            $order->saveProfile('billing', ['email' => $user->email]);

            if($order->getData('checkout_step', 'account') == 'account'){
                $order->saveData(['checkout_step' => 'customer_information']);
            }
        }

        $customer = $order->billingInformation?Customer::getByEmail($order->billingInformation->email):null;
        $canLogin = FALSE;
        $canRegister = FALSE;
        $customerLoggedIn = FALSE;

        $step = $order->getData('checkout_step', 'account');

        $savedAddressOptions = $this->getSavedAddressOptions($request, $order);

        if($customer){
            if($customer->user && Auth::check() && Auth::user()->id == $customer->user_id){
                $customerLoggedIn = TRUE;
            }elseif($customer->user){
                $canLogin = TRUE;
            }
        }

        if($order->billingInformation && $order->billingInformation->email && (!isset($customer->user) || !($customer))){
            $canRegister = TRUE;
        }

        $paymentMethodOptions = $this->getPaymentMethodOptions($request);
        $shippingMethodOptions = $this->getShippingMethodOptions($request, $order);

        $oldValues = old();

        if(!$oldValues){
            if($customerLoggedIn){
                $oldValues['saved_billing_profile'] = $order->getData('saved_billing_profile', $customer->defaultBillingProfile?$customer->defaultBillingProfile->id:null);
                $billingProfile = $oldValues['saved_billing_profile']?Profile::find($oldValues['saved_billing_profile']):$order->billingProfile;

                $oldValues['saved_shipping_profile'] = $order->getData('saved_shipping_profile', $customer->defaultShippingProfile?$customer->defaultShippingProfile->id:null);
                $shippingProfile = $oldValues['saved_shipping_profile']?Profile::find($oldValues['saved_shipping_profile']):$order->shippingProfile;
            }else{
                $billingProfile = $order->billingProfile?:null;
                $shippingProfile = $order->shippingProfile?:null;
            }

            $oldValues['billingProfile'] = $billingProfile?$billingProfile->getDetails():[];
            $oldValues['shippingProfile'] = $shippingProfile?$shippingProfile->getDetails():[];
            $oldValues['shipping_method'] = $order->getSelectedShippingMethod();
            $oldValues['payment_method'] = $order->payment_method_id;
            $oldValues['delivery_date'] = $order->delivery_date?$order->delivery_date->format('Y-m-d'):null;
            $oldValues['additional_fields'] = $order->additional_fields;
            $oldValues['signup_newsletter'] = true;

            Session::flashInput($oldValues);
        }

        $addressOptions = $this->getAddressOptions($request, $order);

        return view($view_name, [
            'order' => $order,
            'step' => $step,
            'customer' => $customer,
            'customerLoggedIn' => $customerLoggedIn,
            'canLogin' => $canLogin,
            'canRegister' => $canRegister,
            'savedAddressOptions' => $savedAddressOptions
        ] + $addressOptions + $shippingMethodOptions + $paymentMethodOptions);
    }

    public function onePageCheckoutProcess(Request $request, $type = 'account')
    {
        $order = FrontendHelper::getCurrentOrder();
        $user = Auth::user();

        $errorCode = 400;
        $errors = [];
        $renderData = null;

        Event::fire(new OrderEvent('before_onepage_checkout_process', $order, ['request' => $request]));

        $viewData = [
            'order' => &$order,
            'customer' => Customer::getByEmail($request->input('billingProfile.email', $order->billingInformation?$order->billingInformation->email:null)),
            'canLogin' => FALSE,
            'customerLoggedIn' => FALSE,
            'canRegister' => FALSE,
            'step' => $order->getData('checkout_step', 'account'),
            'previous_step' => $order->getData('checkout_step', 'account'),
            'success' => []
        ];

        if($request->has('billingProfile.email') && (!isset($viewData['customer']->user) || !isset($viewData['customer']))){
            $viewData['canRegister'] = TRUE;
        }

        $process = $request->input('process');

        if($viewData['customer']){
            if($viewData['customer']->user && Auth::check() && Auth::user()->id == $viewData['customer']->user_id){
                $viewData['customerLoggedIn'] = TRUE;
            }elseif($viewData['customer']->user){
                $viewData['canLogin'] = TRUE;
            }
        }

        switch($type){
            case 'account':
                if($process == 'change'){
                    $viewData['canLogin'] = false;
                    $viewData['canRegister'] = false;
                    $viewData['customerLoggedIn'] = false;

                    if($viewData['previous_step'] == 'account'){
                        //Reset email
                        $order->saveProfile('billing', ['email' => null]);
                    }

                    $nextStep = 'account';
                }elseif($process == 'login'){
                    $this->validate($request, $this->getCheckoutRuleBook('login'));

                    if (Auth::attempt(['email' => $request->input('billingProfile.email'), 'password' => $request->input('password')])) {
                        $nextStep = 'customer_information';
                    }else{
                        $errors['password'] = [trans(LanguageHelper::getTranslationKey('frontend.login.invalid_password'))];
                        $errorCode = 401;

                        $nextStep = 'account';
                    }
                }elseif($process == 'register'){
                    $this->validate($request, $this->getCheckoutRuleBook('register'));

                    $newCustomer = Customer::saveCustomer(['email' => $request->input('billingProfile.email'), 'full_name' => $request->input('name')], ['email' => $request->input('billingProfile.email'), 'password' => $request->input('password')]);
                    if($request->input('signup_newsletter', null) == 1){
                        NewsletterSubscriptionHelper::subscribe('default', $request->input('billingProfile.email'), $request->input('name'));
                    }

                    Auth::login($newCustomer->user);

                    $nextStep = 'customer_information';
                }elseif($process == 'continue_as_guest'){
                    $this->validate($request, $this->getCheckoutRuleBook('continue_as_guest'));

                    $nextStep = 'customer_information';
                }else{
                    $this->validate($request, $this->getCheckoutRuleBook('account'));

                    if($user && (!$viewData['customer'] || $user->id != $viewData['customer']->user_id)){
                        Auth::logout();
                    }

                    //Save customer to order
                    if($viewData['customer']){
                        $order->customer()->associate($viewData['customer']);
                    }

                    //Save email to order
                    $order->saveProfile('billing', ['email' => $request->input('billingProfile.email')]);

                    //If already logged in, next
                    if(Auth::check()){
                        $nextStep = 'customer_information';
                    }else{
                        $nextStep = 'account';
                    }
                }

                break;
            case 'customer_information':
                if($process == 'change'){
                    $savedShippingProfile = Profile::find($request->get('saved_shipping_profile', $order->getData('saved_shipping_profile', null)));

                    if(!$savedShippingProfile){
                        $savedShippingProfile = new Profile();
                    }

                    $order->saveData([
                        'saved_shipping_profile' => $savedShippingProfile->id
                    ]);

                    $order->setRelation('shippingProfile', $savedShippingProfile);

                    $nextStep = 'customer_information';
                }else{
                    $this->validate($request, $this->getCheckoutRuleBook('customer_information'));

                    //Save address
                    if($viewData['customerLoggedIn']){
                        $savedShippingProfile = null;

                        if($request->has('saved_shipping_profile')){
                            $savedShippingProfile = Profile::find($request->input('saved_shipping_profile'));
                        }

                        if(!$savedShippingProfile){
                            $savedShippingProfile = new Profile();
                        }

                        $savedShippingProfile->profileable()->associate($viewData['customer']);

                        if($savedShippingProfile->exists){
                            $viewData['customer']->savedProfiles()->detach($savedShippingProfile->id);
                        }else{
                            $savedShippingProfile->save();
                        }

                        $savedShippingProfile->saveDetails($request->input('shippingProfile'));
                        $viewData['customer']->savedProfiles()->attach([
                            $savedShippingProfile->id => [
                                'shipping' => !$viewData['customer']->defaultShippingProfile,
                                'billing' => !$viewData['customer']->defaultBillingProfile,
                            ]
                        ]);

                        $order->saveData([
                            'saved_shipping_profile' => $savedShippingProfile->id
                        ]);
                    }

                    $order->delivery_date = $request->input('delivery_date', null);
                    $order->saveProfile('shipping', $request->input('shippingProfile'));
                    $order->store()->associate(ProjectHelper::getStoreByRequest($request));

                    $nextStep = 'payment_method';
                }

                break;
            case 'payment_method':
                if($process == 'change'){
                    $nextStep = 'payment_method';
                }else{
                    $this->validate($request, $this->getCheckoutRuleBook('payment_method'));

                    $order->paymentMethod()->associate($request->input('payment_method'));

                    $nextStep = 'checkout_summary';
                }
                break;
            case 'checkout_summary':
                if($process == 'add_coupon'){
                    $rules = [
                        'coupon_code' => 'required|valid_coupon:'.$order->id
                    ];

                    $this->validate($request, $rules);

                    $coupon = CartPriceRule::getCouponByCode($request->input('coupon_code'));

                    $order->addCoupon($coupon);
                    $viewData['success'][] = trans(LanguageHelper::getTranslationKey('frontend.order.coupon_added'), ['coupon_code' => $coupon->coupon_code]);
                }elseif(strpos($process, 'remove_coupon_') !== false){
                    $couponId = str_replace('remove_coupon_', '', $process);
                    $coupon = CartPriceRule::findOrFail($couponId);
                    $order->removeCoupon($coupon);

                    $viewData['success'][] = trans(LanguageHelper::getTranslationKey('frontend.order.coupon_removed'), ['coupon_code' => $coupon->coupon_code]);
                }elseif($process == 'select_shipping_method'){

                }

                $order->currency = $request->input('currency');
                $order->conversion_rate = 1;

                //Process shipping
                if($request->has('shipping_method')){
                    $shippingMethodOptions = $this->getShippingMethodOptions($request, $order)['shippingMethodOptions'];

                    $rules = [
                        'shipping_method' => 'required|in:'.implode(',', array_keys($shippingMethodOptions))
                    ];

                    $this->validate($request, $rules);

                    foreach($shippingMethodOptions as $idx => $shippingMethodOption){
                        if($request->input('shipping_method') == $idx){

                            $order->updateShippingMethod($request->input('shipping_method'), $shippingMethodOption);
                            break;
                        }
                    }
                }

                OrderHelper::processLineItems($request, $viewData['order'], false);

                $order->load('lineItems');
                $order->calculateTotal();

                $nextStep = 'checkout_summary';

                if($process == 'place_order'){
                    $placeOrderRules = [
                        'billingProfile.email' => 'required|email',
                        'shippingProfile.full_name' => 'required',
                        'shippingProfile.phone_number' => 'required',
                        'shipping_method' => 'required'.(isset($shippingMethodOptions)?'|in:'.implode(',', array_keys($shippingMethodOptions)):''),
                        'payment_method' => 'required|exists:payment_methods,id'
                    ];

                    if($order->getShippingMethod() && $order->getShippingMethod()->requireAddress){
                        $placeOrderRules += [
                            'shippingProfile.address_1' => 'required',
                            'shippingProfile.country_id' => 'required',
                            'shippingProfile.state_id' => 'descendant_address:state',
                            'shippingProfile.city_id' => 'descendant_address:city',
                            'shippingProfile.district_id' => 'descendant_address:district',
                            'shippingProfile.area_id' => 'descendant_address:area',
                        ];
                    }

                    if(config('project.enable_delivery_date', FALSE)){
                        $placeOrderRules['delivery_date'] = 'required|date_format:Y-m-d';
                    }

                    $products = [];
                    foreach($order->getProductLineItems() as $idx=>$productLineItem){
                        $placeOrderRules['product.'.$idx] = 'required|exists:products,id,deleted_at,NULL|is_available|is_active|is_in_stock:'.$productLineItem->quantity.'|is_purchaseable';
                        $products[] = $productLineItem->line_item_id;
                    }

                    $coupons = [];
                    foreach($order->getCouponLineItems() as $idx=>$couponLineItem){
                        $placeOrderRules['coupon.'.$idx] = 'valid_coupon:'.$order->id;
                        $coupons[] = $couponLineItem->cartPriceRule->coupon_code;
                    }

                    $validator = Validator::make([
                        'billingProfile' => $order->billingProfile->getDetails(),
                        'shippingProfile' => $order->shippingProfile->getDetails(),
                        'delivery_date' => $order->delivery_date?$order->delivery_date->format('Y-m-d'):null,
                        'shipping_method' => $order->getSelectedShippingMethod(),
                        'payment_method' => $order->paymentMethod?$order->paymentMethod->id:null,
                        'product' => $products,
                        'coupon' => $coupons
                    ], $placeOrderRules);

                    Event::fire(new OrderEvent('frontend_rules_built', $order, ['rules' => &$rules]));

                    if ($validator->fails()) {
                        $errors = $validator->errors()->getMessages();
                        break;
                    }

                    Event::fire(new OrderEvent('before_update_order', $viewData['order']));

                    $order->processStocks();

                    $this->placeOrder($order);

                    if(!ProjectHelper::getConfig('require_billing_information')){
                        //Copy Shipping info to Billing
                        $order->saveProfile('billing', $order->shippingProfile->getDetails());
                    }

                    $profileData = $order->billingInformation->getDetails();
                    Customer::saveCustomer($profileData, null, FALSE);

                    $nextStep = 'complete';
                }
                break;
            default:
                return redirect()->back()->withErrors(['What?']);
                break;
        }

        $renderData = $this->getRenderData($nextStep, $viewData, $request, $order);

        if($viewData['previous_step'] != $viewData['step']){
            $renderData[$viewData['previous_step']] = ProjectHelper::getViewTemplate('frontend.order.one_page.'.$viewData['previous_step']);
        }

        if(!$errors){
            if($request->has('additional_fields')){
                $order->additional_fields = $request->input('additional_fields');
            }

            $order->saveData(['checkout_step' => $viewData['step']]);
            $order->save();
        }

        if($viewData['step'] == 'complete'){
            Event::fire(new OrderUpdate($order, Order::STATUS_CART, true));
            Event::fire(new OrderEvent('customer_place_order', $order));
        }

        if($request->ajax()){
            //Pre-populate profile
            if($order->billingProfile){
                $order->billingProfile->fillDetails();
            }

            if($order->shippingProfile){
                $order->shippingProfile->fillDetails();
            }

            if($errors){
                $response = new JsonResponse($errors, $errorCode);
            }else{
                //If complete
                if($viewData['step'] == 'complete'){
                    $response = new JsonResponse([
                        'data' => [
                            'checkout' => $this->checkoutComplete($request, $order)->render()
                        ],
                        'step' => 'complete',
                        '_token' => csrf_token()
                    ]);
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
            }
        }else{
            //If complete
            if($viewData['step'] == 'complete'){
                $response = redirect()
                    ->route('frontend.order.checkout.complete')
                    ->with('order_id', $order->id)
                    ->with('success', [trans(LanguageHelper::getTranslationKey('frontend.checkout.checkout_complete'))]);
            }else{
                $response = redirect()->back();

                if($errors){
                    $response->withErrors($errors);
                }
            }
        }

        //If ajax, clear old session
        if($request->ajax()){
            //Clear flashed input
            Session::pull('_old_input');
        }

        return $response;
    }

    public function checkoutComplete(Request $request, $order = null)
    {
        //if order exists, it means internal call
        if(!$order){
            $order = Order::find($request->session()->get('order_id', $request->get('debug_order_id')));
        }

        if(!$order || !$order->isCheckout){
            return redirect()->back()->withErrors([trans(LanguageHelper::getTranslationKey('frontend.order.order_not_complete'))]);
        }

        $view_name = ProjectHelper::getViewTemplate('frontend.order.complete');

        return view($view_name, ['order' => $order]);
    }

    public function confirmPayment(Request $request)
    {
        if($request->isMethod('POST')){
            $bankTransfer = PaymentMethod::where('class', 'BankTransfer')->firstOrFail();

            $rules = [
                'order_id' => 'required|exists:orders,reference',
                'details.name' => 'required',
                'details.email' => 'required',
                'payment_date' => 'required|date_format:d/m/Y|before:'.Carbon::now()->modify('+1 day')->format('Y-m-d 00:00:00'),
                'amount' => 'required|numeric',
                'details.account_name' => 'required',
                'details.account_bank' => 'required',
                'attachment' => 'image|max:3000',
            ];

            Event::fire(new OrderEvent('before_validate_confirm_payment', null, ['request' => &$request]));

            $this->validate($request, $rules);

            $order = Order::where('reference', $request->input('order_id'))->first();

            $paymentData = [
                'payment_method_id' => $bankTransfer->id,
                'amount' => $request->input('amount'),
                'currency' => CurrencyHelper::getCurrentCurrency()['code'],
                'status' => Payment::STATUS_PENDING,
                'order_id' => $order->id,
                'notes' => ''
            ];

            $labels = [
                'name' => 'Name',
                'email' => 'Email',
                'phone' => 'Phone',
                'account_name' => 'Account Holder',
                'account_bank' => 'Bank Name',
                'transfer_method' => 'Transfer Method'
            ];

            if($request->has('details')){
                foreach($request->input('details') as $key => $detail){
                    $paymentData['notes'] .= array_get($labels, $key, $key).": ".$detail."\r\n";
                }
            }

            if($request->has('notes')){
                $paymentData['notes'] .= "Notes: ".$request->input('notes');
            }

            $payment = new Payment();
            $payment->fill($paymentData);
            $payment->payment_date = Carbon::createFromFormat('d/m/Y', $request->input('payment_date'));

            Event::fire(new OrderEvent('before_saving_confirm_payment', $order, ['request' => &$request, 'payment' => &$payment]));

            $payment->save();

            if($request->hasFile('attachment')){
                $file = $request->file('attachment');
                $uploadFile = new File();

                if($uploadFile->saveFile($file, false, 'payment_confirmation', ['width' => 2000, 'height' => 2000, 'crop' => 'default'])){
                    $uploadedFiles[] = [
                        'id' => $uploadFile->id,
                        'filename' => $uploadFile->filename,
                        'path' => $uploadFile->folder.$uploadFile->filename
                    ];
                }

                $images[$uploadFile->id] = [
                    'type' => 'attachment',
                ];
                $payment->attachMedia($images, 'attachment');
                $payment->load('attachments');
            }

            EmailHelper::sendMail(ProjectHelper::getConfig('contacts.order.email'), 'New payment confirmation for Order #'.$order->reference, 'payment_confirmation', ['payment' => $payment], 'general', function($message) use ($payment){
                foreach($payment->attachments as $attachment){
                    $message->attach(asset($attachment->getImagePath('original')), [
                        'as' => $attachment->filename,
                    ]);
                }
            });

            return redirect()->back()->with('success', [trans(LanguageHelper::getTranslationKey('frontend.payment_confirmation.success_message'))]);
        }else{
            $view_name = ProjectHelper::getViewTemplate('frontend.order.confirm_payment');

            return view($view_name);
        }
    }

    protected function getRenderData($step, &$viewData, Request $request, Order $order)
    {
        $user = $request->user();
        $loggedInCustomer = $user && $user->customer?$user->customer:null;

        $renderData = [];

        switch($step){
            case 'account':
                $renderData = [
                    'account' => ProjectHelper::getViewTemplate('frontend.order.one_page.account'),
                ];
                break;
            case 'customer_information':
                $savedAddressOptions = $this->getSavedAddressOptions($request, $order);

                $viewData += [
                    'savedAddressOptions' => $savedAddressOptions
                ];

                $addressOptions = $this->getAddressOptions($request, $order);

                $viewData += $addressOptions;

                $renderData = [
                    'customer_information' => ProjectHelper::getViewTemplate('frontend.order.one_page.customer_information')
                ];
                break;
            case 'payment_method':
                $paymentMethodOptions = $this->getPaymentMethodOptions($request);

                $viewData += $paymentMethodOptions;

                $renderData = [
                    'payment_method' => ProjectHelper::getViewTemplate('frontend.order.one_page.payment_method')
                ];
                break;
            case 'checkout_summary':
                $shippingMethodOptions = $this->getShippingMethodOptions($request, $order);

                $viewData += $shippingMethodOptions;

                $renderData = [
                    'order_table' => ProjectHelper::getViewTemplate('frontend.order.one_page.order_table'),
                    'checkout_summary' => ProjectHelper::getViewTemplate('frontend.order.one_page.checkout_summary')
                ];
                break;
            case 'complete':
                break;
            default:
                break;
        }

        $viewData['step'] = $step;

        return $renderData;
    }

    protected function placeOrder(Order $order)
    {
        $order->status = Order::STATUS_PENDING;
        $order->checkout_at = Carbon::now();
        $order->ip_address = RequestFacade::ip();
        $order->user_agent = RequestFacade::header('User-Agent');
        $order->generateReference();

        Event::fire(new OrderEvent('before_order_placed', $order));

        return $order;
    }

    protected function getAddressOptions(Request $request, $order)
    {
        $profileCountryOptions = ['' => trans(LanguageHelper::getTranslationKey('order.address.select_country'))] + AddressHelper::getCountryOptions();
        $profileStateOptions = ['' => trans(LanguageHelper::getTranslationKey('order.address.select_state'))] + AddressHelper::getStateOptions($request->old('billingProfile.country_id', count($profileCountryOptions) < 3?key(array_slice($profileCountryOptions, 1, 1, true)):($order->billingInformation?$order->billingInformation->country_id:null)));
        $profileCityOptions = ['' => trans(LanguageHelper::getTranslationKey('order.address.select_city'))] + AddressHelper::getCityOptions($request->old('billingProfile.state_id', $order->billingInformation?$order->billingInformation->state_id:null));
        $profileDistrictOptions = ['' => trans(LanguageHelper::getTranslationKey('order.address.select_district'))] + AddressHelper::getDistrictOptions($request->old('billingProfile.city_id', $order->billingInformation?$order->billingInformation->city_id:null));
        $profileAreaOptions = ['' => trans(LanguageHelper::getTranslationKey('order.address.select_area'))] + AddressHelper::getAreaOptions($request->old('billingProfile.district_id', $order->billingInformation?$order->billingInformation->district_id:null));

        $shippingCountryOptions = ['' => trans(LanguageHelper::getTranslationKey('order.address.select_country'))] + AddressHelper::getCountryOptions();
        $shippingStateOptions = ['' => trans(LanguageHelper::getTranslationKey('order.address.select_state'))] + AddressHelper::getStateOptions($request->old('shippingProfile.country_id', count($shippingCountryOptions) < 3?key(array_slice($shippingCountryOptions, 1, 1, true)):($order->shippingInformation?$order->shippingInformation->country_id:null)));
        $shippingCityOptions = ['' => trans(LanguageHelper::getTranslationKey('order.address.select_city'))] + AddressHelper::getCityOptions($request->old('shippingProfile.state_id', $order->shippingInformation?$order->shippingInformation->state_id:null));
        $shippingDistrictOptions = ['' => trans(LanguageHelper::getTranslationKey('order.address.select_district'))] + AddressHelper::getDistrictOptions($request->old('shippingProfile.city_id', $order->shippingInformation?$order->shippingInformation->city_id:null));
        $shippingAreaOptions = ['' => trans(LanguageHelper::getTranslationKey('order.address.select_area'))] + AddressHelper::getAreaOptions($request->old('shippingProfile.district_id', $order->shippingInformation?$order->shippingInformation->district_id:null));

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

    protected function getSavedAddressOptions(Request $request, $order)
    {
        $user = $request->user();
        $loggedInCustomer = $user && $user->customer?$user->customer:null;

        $savedAddressOptions = [];

        if($loggedInCustomer && $loggedInCustomer->savedProfiles){
            $savedAddressOptions = [
                '' => trans(LanguageHelper::getTranslationKey('frontend.member.address.create_new_address'))
            ];

            foreach($loggedInCustomer->savedProfiles as $savedProfile){
                $savedProfile->getDetails();
                $savedAddressOptions[$savedProfile->id] = ($savedProfile->pivot->name?Customer::getProfileNameOptions($savedProfile->pivot->name).' - ':'').str_limit($savedProfile->address_1, 50);
            }
        }

        return $savedAddressOptions;
    }

    protected function getShippingMethodOptions(Request $request, $order)
    {
        $shippingMethodOptions = ShippingMethod::getShippingMethods([
            'order' => $order,
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
            'register' => [
                'billingProfile.email' => 'required|email|unique:users,email',
                'name' => 'required',
                'password' => 'required',
            ],
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

        if(ProjectHelper::getConfig('enable_delivery_date', FALSE)){
            $ruleBook['customer_information'] += [
                'delivery_date' => 'required|date_format:Y-m-d'
            ];
        }

        if(ProjectHelper::getConfig('require_billing_information')){
            $ruleBook['customer_information'] += [
                'billingProfile.full_name' => 'required',
                'billingProfile.phone_number' => 'required',
                'billingProfile.address_1' => 'required',
                'billingProfile.state_id' => 'descendant_address:state',
                'billingProfile.city_id' => 'descendant_address:city',
                'billingProfile.district_id' => 'descendant_address:district',
                'billingProfile.area_id' => 'descendant_address:area',
            ];
        }

        return $ruleBook[$type];
    }
}