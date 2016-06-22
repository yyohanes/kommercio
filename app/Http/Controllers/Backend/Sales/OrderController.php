<?php

namespace Kommercio\Http\Controllers\Backend\Sales;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Kommercio\Events\OrderUpdate;
use Kommercio\Facades\AddressHelper;
use Kommercio\Facades\LanguageHelper;
use Kommercio\Facades\OrderHelper;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Customer;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Order\LineItem;
use Kommercio\Http\Requests\Backend\Order\OrderFormRequest;
use Collective\Html\FormFacade;
use Illuminate\Support\Facades\Request as RequestFacade;
use Kommercio\Models\PaymentMethod\PaymentMethod;
use Kommercio\Models\PriceRule\CartPriceRule;
use Kommercio\Models\Product;
use Kommercio\Models\Profile\Profile;
use Kommercio\Facades\PriceFormatter;
use Kommercio\Models\ShippingMethod\ShippingMethod;
use Kommercio\Models\Tax;

class OrderController extends Controller{
    public function index(Request $request)
    {
        $userManagedStores = Auth::user()->getManagedStores();

        $qb = Order::joinBillingProfile()
            ->joinShippingProfile()
            ->belongsToStore($userManagedStores->pluck('id')->all());

        if($request->ajax() || $request->wantsJson()){
            $totalRecords = $qb->count();

            foreach($request->input('filter', []) as $searchKey=>$search){
                if(is_array($search) || trim($search) != ''){
                    if($searchKey == 'billing') {
                        $qb->whereHas('billingProfile', function($qb) use ($search){
                            $qb->whereFields([
                                [
                                    'operator' => 'LIKE',
                                    'key' => 'first_name',
                                    'value' => '%'.$search.'%'
                                ],
                                [
                                    'operator' => 'LIKE',
                                    'key' => 'last_name',
                                    'value' => '%'.$search.'%'
                                ],
                                [
                                    'operator' => 'LIKE',
                                    'key' => 'phone_number',
                                    'value' => '%'.$search.'%'
                                ],
                                [
                                    'operator' => 'LIKE',
                                    'key' => 'email',
                                    'value' => '%'.$search.'%'
                                ],
                                [
                                    'operator' => 'LIKE',
                                    'key' => 'address_1',
                                    'value' => '%'.$search.'%'
                                ],
                                [
                                    'operator' => 'LIKE',
                                    'key' => 'address_2',
                                    'value' => '%'.$search.'%'
                                ],
                                [
                                    'operator' => 'LIKE',
                                    'key' => 'postal_code',
                                    'value' => '%'.$search.'%'
                                ],
                                [
                                    'operator' => 'LIKE',
                                    'key' => 'country',
                                    'value' => '%'.$search.'%'
                                ],
                                [
                                    'operator' => 'LIKE',
                                    'key' => 'state',
                                    'value' => '%'.$search.'%'
                                ],
                                [
                                    'operator' => 'LIKE',
                                    'key' => 'district',
                                    'value' => '%'.$search.'%'
                                ],
                                [
                                    'operator' => 'LIKE',
                                    'key' => 'area',
                                    'value' => '%'.$search.'%'
                                ]
                            ], TRUE);
                        });
                    }elseif($searchKey == 'shipping') {
                        $qb->whereHas('shippingProfile', function($qb) use ($search){
                            $qb->whereFields([
                                [
                                    'operator' => 'LIKE',
                                    'key' => 'first_name',
                                    'value' => '%'.$search.'%'
                                ],
                                [
                                    'operator' => 'LIKE',
                                    'key' => 'last_name',
                                    'value' => '%'.$search.'%'
                                ],
                                [
                                    'operator' => 'LIKE',
                                    'key' => 'phone_number',
                                    'value' => '%'.$search.'%'
                                ],
                                [
                                    'operator' => 'LIKE',
                                    'key' => 'email',
                                    'value' => '%'.$search.'%'
                                ],
                                [
                                    'operator' => 'LIKE',
                                    'key' => 'address_1',
                                    'value' => '%'.$search.'%'
                                ],
                                [
                                    'operator' => 'LIKE',
                                    'key' => 'address_2',
                                    'value' => '%'.$search.'%'
                                ],
                                [
                                    'operator' => 'LIKE',
                                    'key' => 'postal_code',
                                    'value' => '%'.$search.'%'
                                ],
                                [
                                    'operator' => 'LIKE',
                                    'key' => 'country',
                                    'value' => '%'.$search.'%'
                                ],
                                [
                                    'operator' => 'LIKE',
                                    'key' => 'state',
                                    'value' => '%'.$search.'%'
                                ],
                                [
                                    'operator' => 'LIKE',
                                    'key' => 'district',
                                    'value' => '%'.$search.'%'
                                ],
                                [
                                    'operator' => 'LIKE',
                                    'key' => 'area',
                                    'value' => '%'.$search.'%'
                                ]
                            ], TRUE);
                        });
                    }elseif($searchKey == 'reference'){
                        $qb->where($searchKey, 'LIKE', '%'.$search.'%');
                    }elseif($searchKey == 'checkout_at'){
                        if(!empty($search['from'])){
                            $qb->whereRaw('DATE_FORMAT(checkout_at, \'%Y-%m-%d\') >= ?', [$search['from']]);
                        }

                        if(!empty($search['to'])){
                            $qb->whereRaw('DATE_FORMAT(checkout_at, \'%Y-%m-%d\') <= ?', [$search['to']]);
                        }
                    }elseif($searchKey == 'delivery_date'){
                        if(!empty($search['from'])){
                            $qb->whereRaw('DATE_FORMAT(delivery_date, \'%Y-%m-%d\') >= ?', [$search['from']]);
                        }

                        if(!empty($search['to'])){
                            $qb->whereRaw('DATE_FORMAT(delivery_date, \'%Y-%m-%d\') <= ?', [$search['to']]);
                        }
                    }else{
                        $qb->where($searchKey, $search);
                    }
                }
            }

            $filteredRecords = $qb->count();

            $columns = $request->input('columns');
            foreach($request->input('order', []) as $order){
                $orderColumn = $columns[$order['column']];

                $qb->orderBy($orderColumn['name'], $order['dir']);
            }

            if($request->has('length')){
                $qb->take($request->input('length'));
            }

            if($request->has('start') && $request->input('start') > 0){
                $qb->skip($request->input('start'));
            }

            $orders = $qb->get();

            $meat = $this->prepareDatatables($orders, $request->input('start'));

            $data = [
                'draw' => $request->input('draw'),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $meat
            ];

            return response()->json($data);
        }

        $stickyProducts = Product::joinDetail()->selectSelf()->where('sticky_line_item', 1)->orderBy('sort_order', 'ASC')->get();

        return view('backend.order.index', [
            'stickyProducts' => $stickyProducts
        ]);
    }

    protected function prepareDatatables($orders, $orderingStart=0)
    {
        $meat= [];

        $stickyProducts = Product::joinDetail()->selectSelf()->where('sticky_line_item', 1)->orderBy('sort_order', 'ASC')->get();

        foreach($orders as $idx=>$order){
            $orderAction = '';

            $orderAction .= '<div class="btn-group btn-group-xs dropup">';
            $orderAction .= '<a class="btn btn-default" href="'.route('backend.sales.order.view', ['id' => $order->id, 'backUrl' => RequestFacade::fullUrl()]).'"><i class="fa fa-search"></i></a>';

            if($order->isCheckout) {
                $orderAction .= '<a class="btn btn-default" href="' . route('backend.sales.order.print', ['id' => $order->id]) . '" target="_blank"><i class="fa fa-print"></i></a>';
            }

            if(Gate::allows('access', ['process_order'])):
                if(in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_PROCESSING])) {
                    $orderAction .= '<button type="button" class="btn btn-default hold-on-click dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" aria-expanded="true"><i class="fa fa-flag-o"></i></button><ul class="dropdown-menu" role="menu">';
                    if (in_array($order->status, [Order::STATUS_PENDING])) {
                        $orderAction .= '<li><a class="modal-ajax" href="' . route('backend.sales.order.process', ['action' => 'processing', 'id' => $order->id, 'backUrl' => RequestFacade::fullUrl()]) . '"><i class="fa fa-toggle-right"></i> Process</a></li>';
                    }

                    if (in_array($order->status, [Order::STATUS_PENDING, Order::STATUS_PROCESSING])) {
                        $orderAction .= '<li><a class="modal-ajax" href="' . route('backend.sales.order.process', ['action' => 'completed', 'id' => $order->id, 'backUrl' => RequestFacade::fullUrl()]) . '"><i class="fa fa-check-circle"></i> Complete</a></li>';
                        $orderAction .= '<li><a class="modal-ajax" href="' . route('backend.sales.order.process', ['action' => 'cancelled', 'id' => $order->id, 'backUrl' => RequestFacade::fullUrl()]) . '"><i class="fa fa-remove"></i> Cancel</a></li>';
                    }
                    $orderAction .= '</ul>';
                }
            endif;

            $orderAction .= '</div>';

            if($order->isEditable){
                $orderAction .= FormFacade::open(['route' => ['backend.sales.order.delete', 'id' => $order->id], 'class' => 'form-in-btn-group']);
                $orderAction .= '<div class="btn-group btn-group-xs">';

                if(Gate::allows('access', ['edit_order'])):
                    $orderAction .= '<a class="btn btn-default" href="'.route('backend.sales.order.edit', ['id' => $order->id, 'backUrl' => RequestFacade::fullUrl()]).'"><i class="fa fa-pencil"></i></a>';
                endif;

                if($order->isDeleteable) {
                    if (Gate::allows('access', ['delete_order'])):
                        $orderAction .= '<button class="btn btn-default" data-toggle="confirmation" data-original-title="Are you sure?" title="" class="btn-link"><i class="fa fa-trash-o"></i></button></div>';
                    endif;
                }

                $orderAction .= FormFacade::close().'</div>';
            }

            $rowMeat = [
                $idx + 1 + $orderingStart,
                $order->reference,
                $order->checkout_at?$order->checkout_at->format('d M Y, H:i'):'',
                $order->delivery_date?$order->delivery_date->format('d M Y'):'',
                $order->billing_full_name.'<div class="expanded-detail">'.$order->billingInformation->email.'<br/>'.$order->billingInformation->phone_number.'<br/>'.AddressHelper::printAddress($order->billingInformation->getDetails()).'</div>',
                $order->shipping_full_name.'<div class="expanded-detail">'.$order->shippingInformation->email.'<br/>'.$order->shippingInformation->phone_number.'<br/>'.AddressHelper::printAddress($order->billingInformation->getDetails()).'</div>'
            ];

            foreach($stickyProducts as $stickyProduct){
                $rowMeat[] = $order->getProductQuantity($stickyProduct->id);
            }

            $rowMeat = array_merge($rowMeat, [
                PriceFormatter::formatNumber($order->total, $order->currency),
                '<label class="label label-sm bg-'.OrderHelper::getOrderStatusLabelClass($order->status).' bg-font-'.OrderHelper::getOrderStatusLabelClass($order->status).'">'.Order::getStatusOptions($order->status, TRUE).'</label>',
            ]);

            if(Auth::user()->manageMultipleStores){
                $rowMeat = array_merge($rowMeat, [$order->store->name]);
            }

            $rowMeat = array_merge($rowMeat, [$orderAction]);

            $meat[] = $rowMeat;
        }

        return $meat;
    }

    public function create()
    {
        $order = new Order();

        $paymentMethods = PaymentMethod::getPaymentMethods();

        $paymentMethodOptions = [];
        foreach($paymentMethods as $paymentMethod){
            $paymentMethodOptions[$paymentMethod->id] = $paymentMethod->name;
        }

        $lineItems = old('line_items', []);
        $productLineItems = array_pluck($lineItems, 'sku');

        if(!$lineItems){
            $stickyLineItems = Product::joinDetail()->selectSelf()->where('sticky_line_item', 1)->orderBy('sort_order', 'ASC')->get();
            foreach($stickyLineItems as $stickyLineItem){
                if(!in_array($stickyLineItem->sku, $productLineItems)){
                    $lineItems[] = [
                        'line_item_type' => 'product',
                        'line_item_id' => $stickyLineItem->id,
                        'taxable' => $stickyLineItem->taxable,
                        'sku' => $stickyLineItem->sku,
                        'base_price' => $stickyLineItem->getRetailPrice(),
                        'net_price' => $stickyLineItem->getNetPrice(),
                        'quantity' => 0,
                    ];
                }
            }

            Session::flashInput(['line_items' => $lineItems]);
        }

        $shippingMethods = ShippingMethod::getShippingMethods();

        foreach(old('cartPriceRules', []) as $oldCartPriceRule){
            $cartPriceRules[] = CartPriceRule::findOrFail($oldCartPriceRule);
        }

        foreach(old('taxes', []) as $oldTax){
            $taxes[] = Tax::findOrFail($oldTax);
        }

        return view('backend.order.create', [
            'order' => $order,
            'lineItems' => $lineItems,
            'shippingMethods' => $shippingMethods,
            'taxes' => isset($taxes)?$taxes:[],
            'cartPriceRules' => isset($cartPriceRules)?$cartPriceRules:[],
            'paymentMethodOptions' => $paymentMethodOptions
        ]);
    }

    public function store(OrderFormRequest $request)
    {
        $order = new Order();
        $originalStatus = null;

        $customer = null;

        $order->notes = $request->input('notes');
        $order->delivery_date = $request->input('delivery_date', null);
        $order->store_id = $request->input('store_id');
        $order->payment_method_id = $request->input('payment_method', null);
        $order->currency = $request->input('currency');
        $order->conversion_rate = 1;
        $order->save();

        $order->saveProfile('billing', $request->input('profile'));
        $order->saveProfile('shipping', $request->input('shipping_profile'));

        $this->processLineItems($request, $order);

        $order->load('lineItems');
        $order->processStocks();
        $order->calculateTotal();

        if($request->input('action') == 'place_order'){
            $this->placeOrder($order);

            $profileData = $request->input('profile');

            $customer = Customer::saveCustomer($profileData);
        }else{
            $order->status = Order::STATUS_ADMIN_CART;
        }

        if($customer){
            $order->customer()->associate($customer);
        }

        $order->save();

        Event::fire(new OrderUpdate($order, $originalStatus, $request->input('send_notification')));

        return redirect()->route('backend.sales.order.index')->with('success', ['This '.Order::getStatusOptions($order->status, true).' order has successfully been created.']);
    }

    public function view($id)
    {
        $order = Order::findOrFail($id);
        $lineItems = $order->lineItems;
        $billingProfile = $order->billingProfile?$order->billingProfile->fillDetails():new Profile();
        $shippingProfile = $order->shippingProfile?$order->shippingProfile->fillDetails():new Profile();

        return view('backend.order.view', [
            'order' => $order,
            'lineItems' => $lineItems,
            'billingProfile' => $billingProfile,
            'shippingProfile' => $shippingProfile
        ]);
    }

    public function printOrder($id)
    {
        $order = Order::findOrFail($id);
        $billingProfile = $order->billingProfile?$order->billingProfile->fillDetails():new Profile();
        $shippingProfile = $order->shippingProfile?$order->shippingProfile->fillDetails():new Profile();

        return view('print.order.invoice', [
            'order' => $order,
            'billingProfile' => $billingProfile,
            'shippingProfile' => $shippingProfile
        ]);
    }

    public function edit($id)
    {
        $order = Order::findOrFail($id);

        $lineItems = old('line_items', $order->lineItems);

        $paymentMethods = PaymentMethod::getPaymentMethods();

        $paymentMethodOptions = [];
        foreach($paymentMethods as $paymentMethod){
            $paymentMethodOptions[$paymentMethod->id] = $paymentMethod->name;
        }

        $oldValues = old();

        if(!$oldValues){
            $oldValues['existing_customer'] = $order->customer?$order->customer->getProfile()->email:null;
            $oldValues['profile'] = $order->billingProfile?$order->billingProfile->getDetails():[];
            $oldValues['shipping_profile'] = $order->shippingProfile?$order->shippingProfile->getDetails():[];
            $oldValues['payment_method'] = $order->payment_method_id;

            foreach($lineItems as $lineItem){
                $lineItemData = $lineItem->toArray();
                $lineItemTotal = $lineItem->calculateTotal();

                if($lineItem->isProduct){
                    $lineItemData['id'] = $lineItem->product->id;
                    $lineItemData['sku'] = $lineItem->product->sku;
                }

                if($lineItem->isShipping){
                    $lineItemData['shipping_method'] = $lineItem->getData('shipping_method');
                }

                if($lineItem->isCartPriceRule){
                    $oldValues['cart_price_rules'][] = $lineItem->line_item_id;
                }

                if($lineItem->isCoupon){
                    $oldValues['added_coupons'][] = $lineItem->line_item_id;
                }

                if($lineItem->isTax){
                    $oldValues['taxes'][] = $lineItem->line_item_id;
                }

                $lineItemData['lineitem_total_amount'] = $lineItemTotal;

                $oldValues['line_items'][] = $lineItemData;
            }

            Session::flashInput($oldValues);
        }

        foreach(old('cart_price_rules', []) as $oldCartPriceRule){
            $cartPriceRules[] = CartPriceRule::findOrFail($oldCartPriceRule);
        }

        foreach(old('taxes', []) as $oldTax){
            $taxes[] = Tax::findOrFail($oldTax);
        }

        return view('backend.order.edit', [
            'order' => $order,
            'lineItems' => $lineItems,
            'taxes' => isset($taxes)?$taxes:[],
            'cartPriceRules' => isset($cartPriceRules)?$cartPriceRules:[],
            'paymentMethodOptions' => $paymentMethodOptions,
            'editOrder' => $order->isCheckout?true:false
        ]);
    }

    public function update(OrderFormRequest $request, $id)
    {
        $order = Order::findOrFail($id);
        $originalStatus = $order->status;

        $customer = null;

        $order->delivery_date = $request->input('delivery_date', null);
        $order->notes = $request->input('notes');
        $order->store_id = $request->input('store_id');
        $order->payment_method_id = $request->input('payment_method', null);
        $order->currency = $request->input('currency');
        $order->conversion_rate = 1;

        $order->saveProfile('billing', $request->input('profile'));
        $order->saveProfile('shipping', $request->input('shipping_profile'));

        //If PENDING order is updated, return all stocks first
        if($order->status == Order::STATUS_PENDING){
            $order->returnStocks();
        }

        $this->processLineItems($request, $order);

        $order->load('lineItems');
        $order->processStocks();
        $order->calculateTotal();

        if($request->input('action') == 'place_order'){
            $this->placeOrder($order);

            $profileData = $request->input('profile');

            $customer = Customer::saveCustomer($profileData);
        }else{

        }

        if($customer){
            $order->customer()->associate($customer);
        }

        $order->save();

        Event::fire(new OrderUpdate($order, $originalStatus, $request->input('send_notification')));

        return redirect()->route('backend.sales.order.index')->with('success', ['This '.Order::getStatusOptions($order->status, true).' order has successfully been updated.']);
    }

    public function delete($id)
    {
        $order = Order::findOrFail($id);

        $order->delete();

        return redirect()->route('backend.sales.order.index')->with('success', ['This '.Order::getStatusOptions($order->status, true).' order has successfully been deleted.']);
    }

    public function deleteAll()
    {
        $orders = Order::all();

        foreach($orders as $order){
            $order->forceDelete();
        }

        return redirect()->route('backend.sales.order.index')->with('success', ['All orders have been deleted.']);
    }

    public function process(Request $request, $process, $id=null)
    {
        $order = Order::find($id);

        if($request->isMethod('GET')){
            $options = [
                'order' => $order,
                'backUrl' => $request->get('backUrl', route('backend.sales.order.index'))
            ];

            switch($process){
                case 'pending':
                    $processForm = 'pending_form';
                    break;
                case 'processing':
                    $processForm = 'processing_form';
                    break;
                case 'completed':
                    $processForm = 'completed_form';
                    break;
                case 'cancelled':
                    $processForm = 'cancelled_form';
                    break;
                default:
                    return response('No process is selected.');
                    break;
            }

            return view('backend.order.process.'.$processForm, $options);
        }else{
            $originalStatus = $order->status;

            switch($process){
                case 'processing':
                    $order->status = Order::STATUS_PROCESSING;
                    $message = 'Order has been set to <span class="label bg-'.OrderHelper::getOrderStatusLabelClass($order->status).' bg-font-'.OrderHelper::getOrderStatusLabelClass($order->status).'">Processing.</span>';
                    break;
                case 'completed':
                    $order->status = Order::STATUS_COMPLETED;
                    $message = 'Order has been <span class="label bg-'.OrderHelper::getOrderStatusLabelClass($order->status).' bg-font-'.OrderHelper::getOrderStatusLabelClass($order->status).'">Completed.</span>';
                    break;
                case 'cancelled':
                    $order->returnStocks();
                    $order->status = Order::STATUS_CANCELLED;
                    $message = 'Order has been <span class="label bg-'.OrderHelper::getOrderStatusLabelClass($order->status).' bg-font-'.OrderHelper::getOrderStatusLabelClass($order->status).'">Cancelled.</span>';
                    break;
                default:
                    $message = 'No process has been done.';
                    break;
            }

            $order->save();

            Event::fire(new OrderUpdate($order, $originalStatus, $request->input('send_notification')));

            return redirect($request->input('backUrl', route('backend.sales.order.index')))->with('success', [$message]);
        }
    }

    public function copyCustomerInformation(Request $request, $type, $profile_id = null)
    {
        if($profile_id){
            $profile = Profile::findOrFail($profile_id);
            $profileDetails = $profile->getDetails();
        }else{
            $profileDetails = $request->input($request->input('source'));
        }

        //Flash to internally rendered form. We will remove flashed data later
        Session::flashInput([$type => $profileDetails]);

        $render = view('backend.order.customer_information', ['type' => $type])->render();

        Session::remove('_old_input');

        return response()->json(['data' => $render, '_token' => csrf_token()]);
    }

    public function lineItemRow(Request $request, $type, $id=null)
    {
        switch($type){
            case 'product':
                $model = Product::findOrFail($id);
                break;
            default:
                break;
        }

        $render = view('backend.order.line_items.form.product', [
            'product' => $model,
            'key' => $request->get('product_index')
        ])->render();

        return response()->json(['data' => $render, '_token' => csrf_token()]);
    }

    public function shippingOptions(Request $request)
    {
        $return = [];

        $order = OrderHelper::createDummyOrderFromRequest($request);

        $shippingOptions = ShippingMethod::getShippingMethods([
            'subtotal' => $order->calculateSubtotal()
        ]);

        foreach($shippingOptions as $idx=>$shippingOption){
            $return[$idx] = [
                'shipping_method_id' => $shippingOption['shipping_method_id'],
                'name' => $shippingOption['name'],
                'price' => $shippingOption['price'],
                'taxable' => $shippingOption['taxable']
            ];
        }

        return response()->json($return);
    }

    public function getCartRules(Request $request, $internal = false)
    {
        $order = OrderHelper::createDummyOrderFromRequest($request);

        $subtotal = $order->calculateProductTotal() + $order->calculateAdditionalTotal();

        $shippings = [];
        foreach($order->getShippingLineItems() as $shippingLineItem){
            $shippings[] = $shippingLineItem->line_item_id;
        }

        $options = [
            'subtotal' => $subtotal,
            'currency' => $order->currency,
            'store_id' => $order->store_id,
            'customer_email' => $order->customer?$order->customer->getProfile()->email:null,
            'shippings' => $shippings,
            'added_coupons' => $request->input('added_coupons', [])
        ];

        $priceRules = CartPriceRule::getCartPriceRules($options);

        foreach($priceRules as $idx=>$priceRule){
            if(!$priceRule->validateUsage($options['customer_email'])['valid']){
                unset($priceRules[$idx]);
            }
        }

        if($request->ajax() || !$internal){
            return response()->json([
                'data' => $priceRules,
                '_token' => csrf_token()
            ]);
        }else{
            return $priceRules;
        }
    }

    public function addCoupon(Request $request)
    {
        $couponCode = $request->input('coupon_code', 'ERRORCOUPON');
        if(!CartPriceRule::getCouponByCode($couponCode)){
            return new JsonResponse([
                'coupon_code' => [trans(LanguageHelper::getTranslationKey('order.coupons.not_exist'))]
            ], 422);
        }

        $order = OrderHelper::createDummyOrderFromRequest($request);

        $subtotal = $order->calculateProductTotal() + $order->calculateAdditionalTotal();

        $shippings = [];
        foreach($order->getShippingLineItems() as $shippingLineItem){
            $shippings[] = $shippingLineItem->line_item_id;
        }

        $options = [
            'subtotal' => $subtotal,
            'currency' => $order->currency,
            'store_id' => $order->store_id,
            'customer_email' => $order->customer?$order->customer->getProfile()->email:null,
            'shippings' => $shippings,
            'coupon_code' => $couponCode,
            'added_coupons' => $request->input('added_coupons', [])
        ];

        $couponPriceRules = CartPriceRule::getCartPriceRules($options);

        if($couponPriceRules->count() < 1){
            return new JsonResponse([
                'coupon_code' => [trans(LanguageHelper::getTranslationKey('order.coupons.invalid'))]
            ], 422);
        }else{
            foreach($couponPriceRules as $couponPriceRule){
                $couponValidation = $couponPriceRule->validateUsage($options['customer_email']);
                if(!$couponValidation['valid']){
                    return new JsonResponse([
                        'coupon_code' => [trans(LanguageHelper::getTranslationKey($couponValidation['message']))]
                    ], 422);
                }
            }
        }

        if($request->ajax()){
            return response()->json([
                'data' => $couponPriceRules,
                '_token' => csrf_token()
            ]);
        }else{
            return $couponPriceRules;
        }
    }

    protected function processLineItems(Request $request, $order)
    {
        $dummyOrder = OrderHelper::createDummyOrderFromRequest($request);
        $dummyOrderSubtotal = $dummyOrder->calculateSubtotal() + $dummyOrder->calculateAdditionalTotal();

        $cartPriceRules = $this->getCartRules($request, true);
        $productCartPriceRules = [];
        $orderCartPriceRules = [];
        $taxes = Tax::getTaxes([
            'country_id' => $request->input('profile.country_id', null),
            'state_id' => $request->input('profile.state_id', null),
            'city_id' => $request->input('profile.city_id', null),
            'district_id' => $request->input('profile.district_id', null),
            'area_id' => $request->input('profile.area_id', null),
        ]);

        foreach($cartPriceRules as $cartPriceRule){

            if($cartPriceRule->offer_type == CartPriceRule::OFFER_TYPE_ORDER_DISCOUNT){
                $orderCartPriceRules[] = $cartPriceRule;
            }elseif($cartPriceRule->offer_type == CartPriceRule::OFFER_TYPE_PRODUCT_DISCOUNT){
                $productCartPriceRules[] = $cartPriceRule;
            }
        }

        $existingLineItems = $order->lineItems->all();
        $count = 0;

        foreach($request->input('line_items') as $lineItemDatum){
            if($lineItemDatum['line_item_type'] == 'product' && empty($lineItemDatum['quantity'])){
                continue;
            }

            $lineItem = $this->reuseOrCreateLineItem($order, $existingLineItems, $count);

            $lineItem->processData($lineItemDatum, $count);
            $lineItemAmount = $lineItem->net_price;

            if($lineItem->isProduct){
                foreach($productCartPriceRules as $productCartPriceRule){
                    $lineItemAmount = $productCartPriceRule->getValue($lineItemAmount);
                    $productCartPriceRule->total += ($lineItemAmount - $lineItem->net_price) * $lineItem->quantity;
                }
            }

            if($lineItem->taxable){
                foreach($taxes as $tax){
                    $taxValue = [
                        'net' => 0,
                        'gross' => 0
                    ];

                    $taxValue['gross'] = PriceFormatter::round($tax->calculateTax($lineItemAmount));
                    $taxValue['net'] = PriceFormatter::round($taxValue['gross']);

                    $order->rounding_total = PriceFormatter::calculateRounding($taxValue['gross'], $taxValue['net']) * $lineItem->quantity;

                    $tax->total += $taxValue['net'] * $lineItem->quantity;
                }
            }

            $lineItem->calculateTotal();
            $lineItem->save();

            $count += 1;
        }

        foreach($productCartPriceRules as $productCartPriceRule){
            $priceRuleLineItemDatum = [
                'cart_price_rule_id' => $productCartPriceRule->id,
                'line_item_type' => 'cart_price_rule',
                'lineitem_total_amount' => $productCartPriceRule->total,
            ];

            $lineItem = $this->reuseOrCreateLineItem($order, $existingLineItems, $count);

            $lineItem->processData($priceRuleLineItemDatum, $count);
            $lineItem->save();

            $count += 1;
        }

        foreach($orderCartPriceRules as $orderCartPriceRule){
            $value = $orderCartPriceRule->getValue($dummyOrderSubtotal);
            $orderCartPriceRule->total = $value - $dummyOrderSubtotal;

            $priceRuleLineItemDatum = [
                'cart_price_rule_id' => $orderCartPriceRule->id,
                'line_item_type' => 'cart_price_rule',
                'lineitem_total_amount' => $orderCartPriceRule->total,
            ];

            $lineItem = $this->reuseOrCreateLineItem($order, $existingLineItems, $count);

            $lineItem->processData($priceRuleLineItemDatum, $count);
            $lineItem->save();

            $count += 1;
        }

        foreach($taxes as $tax){
            $taxLineItemDatum = [
                'tax_id' => $tax->id,
                'line_item_type' => 'tax',
                'lineitem_total_amount' => $tax->total,
            ];

            $lineItem = $this->reuseOrCreateLineItem($order, $existingLineItems, $count);

            $lineItem->processData($taxLineItemDatum, $count);
            $lineItem->save();

            $count += 1;
        }

        //Delete unused line items
        foreach($existingLineItems as $existingLineItem){
            $existingLineItem->delete();
        }
    }

    protected function reuseOrCreateLineItem($order, &$existingLineItems, $count)
    {
        if(!isset($existingLineItems[$count])){
            $lineItem = new LineItem();
            $lineItem->order()->associate($order);
        }else{
            //Clone and reset existing line item and will eventually be updated with new data
            $lineItem = $existingLineItems[$count];
            unset($existingLineItems[$count]);

            $lineItem->clearData();
        }

        return $lineItem;
    }

    protected function placeOrder(Order $order)
    {
        $order->status = Order::STATUS_PENDING;
        $order->checkout_at = Carbon::now();
        $order->generateReference();

        //Call all shipping processing methods
        foreach($order->getShippingLineItems() as $shippingLineItem){
            $shippingLineItem->shippingMethod->getProcessor()->beforePlaceOrder($order);
        }

        return $order;
    }
}