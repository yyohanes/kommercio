<?php

namespace Kommercio\Http\Controllers\Backend\Sales;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Kommercio\Events\OrderEvent;
use Kommercio\Events\OrderUpdate;
use Kommercio\Facades\AddressHelper;
use Kommercio\Facades\LanguageHelper;
use Kommercio\Facades\OrderHelper;
use Kommercio\Facades\ProjectHelper;
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
use Maatwebsite\Excel\Facades\Excel;

class OrderController extends Controller{
    public function index(Request $request)
    {
        $userManagedStores = Auth::user()->getManagedStores();

        $qb = Order::joinBillingProfile()
            ->joinShippingProfile()
            ->joinOutstanding()
            ->belongsToStore($userManagedStores->pluck('id')->all())
            ->where('status', '<>', Order::STATUS_CART);

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
                    }elseif($searchKey == 'outstanding') {
                        if($search == 'settled'){
                            $qb->whereRaw('(total - COALESCE(P.paid_amount, 0)) <= ?', [0]);
                        }else{
                            $qb->whereRaw('(total - COALESCE(P.paid_amount, 0)) > ?', [0]);
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
        
        if(config('project.enable_store_selector', false)){
            $store_id = ProjectHelper::getActiveStore()->id;
        }else{
            $store_id = ProjectHelper::getDefaultStore()->id;
        }
        $stickyProducts = Product::joinDetail($store_id)->selectSelf()->active()->where('sticky_line_item', 1)->orderBy('sort_order', 'ASC')->get();

        return view('backend.order.index', [
            'stickyProducts' => $stickyProducts
        ]);
    }

    protected function prepareDatatables($orders, $orderingStart=0)
    {
        $meat= [];

        if(config('project.enable_store_selector', false)){
            $store_id = ProjectHelper::getActiveStore()->id;
        }else{
            $store_id = ProjectHelper::getDefaultStore()->id;
        }
        $stickyProducts = Product::joinDetail($store_id)->selectSelf()->active()->where('sticky_line_item', 1)->orderBy('sort_order', 'ASC')->get();

        foreach($orders as $idx=>$order){
            $orderAction = '';

            $orderAction .= FormFacade::open(['route' => ['backend.sales.order.delete', 'id' => $order->id], 'class' => 'form-in-btn-group']);
            $orderAction .= '<div class="btn-group btn-group-xs">';

            $orderAction .= '<a class="btn btn-default" href="'.route('backend.sales.order.view', ['id' => $order->id, 'backUrl' => RequestFacade::fullUrl()]).'"><i class="fa fa-search"></i></a>';

            if($order->isEditable) {
                if (Gate::allows('access', ['edit_order'])):
                    $orderAction .= '<a class="btn btn-default" href="' . route('backend.sales.order.edit', ['id' => $order->id, 'backUrl' => RequestFacade::fullUrl()]) . '"><i class="fa fa-pencil"></i></a>';
                endif;

                if ($order->isDeleteable) {
                    if (Gate::allows('access', ['delete_order'])):
                        $orderAction .= '<button class="btn btn-default" data-toggle="confirmation" data-original-title="Are you sure?" title="" class="btn-link"><i class="fa fa-trash-o"></i></button></div>';
                    endif;
                }
            }

            $orderAction .= FormFacade::close().'</div>';

            $processActions = '';
            if (Gate::allows('access', ['process_order']) && $order->isProcessable) {
                $processActions .= '<li><a class="modal-ajax" href="' . route('backend.sales.order.process', ['action' => 'processing', 'id' => $order->id, 'backUrl' => RequestFacade::fullUrl()]) . '"><i class="fa fa-toggle-right"></i> Process</a></li>';
            }

            if(Gate::allows('access', ['ship_order']) && $order->isShippable):
                $processActions .= '<li><a class="modal-ajax" href="' . route('backend.sales.order.process', ['action' => 'shipped', 'id' => $order->id, 'backUrl' => RequestFacade::fullUrl()]) . '"><i class="fa fa-truck"></i> Ship</a></li>';
            endif;
            if(Gate::allows('access', ['complete_order']) && $order->isCompleteable):
                $processActions .= '<li><a class="modal-ajax" href="' . route('backend.sales.order.process', ['action' => 'completed', 'id' => $order->id, 'backUrl' => RequestFacade::fullUrl()]) . '"><i class="fa fa-check-circle"></i> Complete</a></li>';
            endif;
            if(Gate::allows('access', ['cancel_order']) && $order->isCancellable):
                $processActions .= '<li><a class="modal-ajax" href="' . route('backend.sales.order.process', ['action' => 'cancelled', 'id' => $order->id, 'backUrl' => RequestFacade::fullUrl()]) . '"><i class="fa fa-remove"></i> Cancel</a></li>';
            endif;

            if(!empty($processActions)){
                $orderAction .= '<div class="btn-group btn-group-xs dropup"><button type="button" class="btn btn-default hold-on-click dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" aria-expanded="true"><i class="fa fa-flag-o"></i></button><ul class="dropdown-menu pull-right" role="menu">'.$processActions.'</ul></div>';
            }

            $printActions = '';
            if(Gate::allows('access', ['print_invoice']) && $order->isPrintable):
                $printActions .= '<li><a href="' . route('backend.sales.order.print', ['id' => $order->id]) . '" target="_blank">Invoice</a></li>';
            endif;
            if(Gate::allows('access', ['print_delivery_note']) && $order->isPrintable && config('project.enable_delivery_note', false)):
                $printActions .= '<li><a href="' . route('backend.sales.order.print', ['id' => $order->id, 'type' => 'delivery_note']) . '" target="_blank">Delivery Note</a></li>';
            endif;

            if(!empty($printActions)){
                $orderAction .= '<div class="btn-group btn-group-xs dropup"><button type="button" class="btn btn-default hold-on-click dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" aria-expanded="true"><i class="fa fa-print"></i></button><ul class="dropdown-menu pull-right" role="menu">'.$printActions.'</ul></div>';
            }

            $rowMeat = [
                $idx + 1 + $orderingStart,
                $order->reference,
                $order->checkout_at?$order->checkout_at->format('d M Y, H:i'):'',
                $order->delivery_date?$order->delivery_date->format('d M Y'):'',
                $order->billing_full_name.'<div class="expanded-detail">'.$order->billingInformation->email.'<br/>'.$order->billingInformation->phone_number.'<br/>'.AddressHelper::printAddress($order->billingInformation->getDetails()).'</div>',
                $order->shipping_full_name.'<div class="expanded-detail">'.$order->shippingInformation->email.'<br/>'.$order->shippingInformation->phone_number.'<br/>'.AddressHelper::printAddress($order->billingInformation->getDetails()).'</div>',
            ];

            foreach($stickyProducts as $stickyProduct){
                $rowMeat[] = $order->getProductQuantity($stickyProduct->id);
            }

            $orderTotal = PriceFormatter::formatNumber($order->total, $order->currency).'<div class="expanded-detail" data-ajax_load="'.route('backend.sales.order.quick_content_view', ['id' => $order->id]).'"></div>';
            $rowMeat = array_merge($rowMeat, [$orderTotal]);

            if(Gate::allows('access', ['view_payment'])):
                $rowMeat = array_merge($rowMeat, [
                    '<label class="label label-sm label-'.($order->outstanding > 0?'warning':'success').'">'.PriceFormatter::formatNumber($order->outstanding).'</label>',
                ]);
            endif;

            $rowMeat = array_merge($rowMeat, ['<label class="label label-sm bg-'.OrderHelper::getOrderStatusLabelClass($order->status).' bg-font-'.OrderHelper::getOrderStatusLabelClass($order->status).'">'.Order::getStatusOptions($order->status, TRUE).'</label>']);

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
            if(config('project.enable_store_selector', false)){
                $store_id = ProjectHelper::getActiveStore()->id;
            }else{
                $store_id = ProjectHelper::getDefaultStore()->id;
            }

            $stickyLineItems = Product::joinDetail($store_id)->selectSelf()->active()->where('sticky_line_item', 1)->orderBy('sort_order', 'ASC')->get();
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

        foreach(old('cartPriceRules', []) as $oldCartPriceRule){
            $cartPriceRules[] = CartPriceRule::findOrFail($oldCartPriceRule);
        }

        foreach(old('taxes', []) as $oldTax){
            $taxes[] = Tax::findOrFail($oldTax);
        }

        return view('backend.order.create', [
            'order' => $order,
            'lineItems' => $lineItems,
            'taxes' => isset($taxes)?$taxes:[],
            'cartPriceRules' => isset($cartPriceRules)?$cartPriceRules:[],
            'paymentMethodOptions' => $paymentMethodOptions,
        ]);
    }

    public function store(OrderFormRequest $request)
    {
        $order = new Order();
        $originalStatus = null;

        $customer = null;

        $order->notes = $request->input('notes');
        $order->delivery_date = $request->input('delivery_date', null);
        $order->payment_method_id = $request->input('payment_method', null);
        $order->currency = $request->input('currency');
        $order->conversion_rate = 1;
        if($request->has('additional_fields')){
            $order->additional_fields = $request->input('additional_fields');
        }

        $store = ProjectHelper::getStoreByRequest($request);
        $order->store()->associate($store);

        $order->save();

        $order->saveProfile('billing', $request->input('profile'));
        $order->saveProfile('shipping', $request->input('shipping_profile'));

        OrderHelper::processLineItems($request, $order);

        $order->load('lineItems');
        $order->processStocks();
        $order->calculateTotal();

        Event::fire(new OrderEvent('before_update_order', $order));

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
            'shippingProfile' => $shippingProfile,
            'internalMemos' => $order->internalMemos
        ]);
    }

    public function quickContentView($id)
    {
        $order = Order::findOrFail($id);
        $lineItems = $order->lineItems;

        return view('backend.order.quick_content_view', [
            'order' => $order,
            'lineItems' => $lineItems,
        ]);
    }

    public function printOrder(Request $request, $id, $type='invoice')
    {
        $user = $request->user();
        $order = Order::findOrFail($id);

        if($type == 'delivery_note'){
            OrderHelper::saveOrderComment('Print Delivery Note.', 'print_delivery_note', $order, $user);

            if(config('project.print_format', config('kommercio.print_format')) == 'xls'){
                Excel::create('Delivery Note #'.$order->reference, function($excel) use ($order) {
                    $excel->setDescription('Delivery Note #'.$order->reference);
                    $excel->sheet('Sheet 1', function($sheet) use ($order){
                        $sheet->loadView(ProjectHelper::getViewTemplate('print.order.delivery_note'), [
                            'order' => $order
                        ]);
                    });
                })->download('xls');
            }

            return view(ProjectHelper::getViewTemplate('print.order.delivery_note'), [
                'order' => $order
            ]);
        }

        OrderHelper::saveOrderComment('Print Invoice.', 'print_invoice', $order, $user);

        if(config('project.print_format', config('kommercio.print_format')) == 'xls'){
            Excel::create('Invoice #'.$order->reference, function($excel) use ($order) {
                $excel->setDescription('Invoice #'.$order->reference);
                $excel->sheet('Sheet 1', function($sheet) use ($order){
                    $sheet->loadView(ProjectHelper::getViewTemplate('print.order.invoice'), [
                        'order' => $order
                    ]);
                });
            })->download('xls');
        }

        return view(ProjectHelper::getViewTemplate('print.order.invoice'), [
            'order' => $order
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
            $oldValues['store_id'] = $order->store_id;

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
        $order->payment_method_id = $request->input('payment_method', null);
        $order->currency = $request->input('currency');

        $store = ProjectHelper::getStoreByRequest($request);
        $order->store()->associate($store);

        if($request->has('additional_fields')){
            $order->additional_fields = $request->input('additional_fields');
        }
        $order->conversion_rate = 1;

        $order->saveProfile('billing', $request->input('profile'));
        $order->saveProfile('shipping', $request->input('shipping_profile'));

        //If PENDING order is updated, return all stocks first
        if($order->status == Order::STATUS_PENDING){
            $order->returnStocks();
        }

        OrderHelper::processLineItems($request, $order);

        $order->load('lineItems');
        $order->processStocks();
        $order->calculateTotal();

        Event::fire(new OrderEvent('before_update_order', $order));

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
        $user = $request->user();
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
                case 'shipped':
                    $processForm = 'shipped_form';
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
                    if(!Gate::allows('access', ['process_order'])){
                        abort(403);
                    }
                    $order->status = Order::STATUS_PROCESSING;
                    $message = 'Order has been set to <span class="label bg-'.OrderHelper::getOrderStatusLabelClass($order->status).' bg-font-'.OrderHelper::getOrderStatusLabelClass($order->status).'">Processing.</span>';
                    break;
                case 'shipped':
                    if(!Gate::allows('access', ['ship_order'])){
                        abort(403);
                    }

                    if(!empty($request->input('tracking_number'))){
                        $order->saveData([
                            'tracking_number' => $request->input('tracking_number')
                        ]);
                    }

                    if(!empty($request->input('delivered_by'))){
                        $order->saveData([
                            'delivered_by' => $request->input('delivered_by')
                        ]);

                        OrderHelper::saveOrderComment('Order was delivered by '.$request->input('delivered_by'), 'delivered_by', $order, $user);
                    }

                    $order->status = Order::STATUS_SHIPPED;

                    $message = 'Order has been set to <span class="label bg-'.OrderHelper::getOrderStatusLabelClass($order->status).' bg-font-'.OrderHelper::getOrderStatusLabelClass($order->status).'">Shipped.</span>';
                    break;
                case 'completed':
                    if(!Gate::allows('access', ['complete_order'])){
                        abort(403);
                    }
                    $order->status = Order::STATUS_COMPLETED;
                    $message = 'Order has been <span class="label bg-'.OrderHelper::getOrderStatusLabelClass($order->status).' bg-font-'.OrderHelper::getOrderStatusLabelClass($order->status).'">Completed.</span>';
                    break;
                case 'cancelled':
                    if(!Gate::allows('access', ['cancel_order'])){
                        abort(403);
                    }

                    $rules = [
                        'notes' => 'required'
                    ];

                    $this->validate($request, $rules);

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
            'order' => $order,
            'request' => $request
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
        $priceRules = OrderHelper::getCartRules($request);

        if($request->ajax() || !$internal){
            $returnedData = [];

            foreach($priceRules as $priceRule){
                $returnedData[] = $priceRule->toArray() + ['products' => array_keys($priceRule->getProducts())];
            }

            return response()->json([
                'data' => $returnedData,
                '_token' => csrf_token()
            ]);
        }else{
            return $priceRules;
        }
    }

    public function addCoupon(Request $request)
    {
        $couponCode = $request->input('coupon_code', 'ERRORCOUPON');

        $couponPriceRules = CartPriceRule::addCoupon($couponCode, $request);

        if(is_string($couponPriceRules)){
            return new JsonResponse([
                'coupon_code' => [$couponPriceRules]
            ], 422);
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