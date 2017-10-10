<?php

namespace Kommercio\Http\Controllers\Backend\Sales;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Kommercio\Events\CouponEvent;
use Kommercio\Events\DeliveryOrderEvent;
use Kommercio\Events\OrderEvent;
use Kommercio\Events\OrderUpdate;
use Kommercio\Facades\AddressHelper;
use Kommercio\Facades\LanguageHelper;
use Kommercio\Facades\OrderHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Facades\RuntimeCache;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Customer;
use Kommercio\Models\Order\DeliveryOrder\DeliveryOrder;
use Kommercio\Models\Order\Invoice;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Order\LineItem;
use Kommercio\Http\Requests\Backend\Order\OrderFormRequest;
use Collective\Html\FormFacade;
use Illuminate\Support\Facades\Request as RequestFacade;
use Kommercio\Models\Order\OrderComment;
use Kommercio\Models\Order\OrderLimit;
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
                        $qb->where(function($innerQb) use ($search){
                            $innerQb->whereRaw('CONCAT_WS(" ", BFNAME.value, BLNAME.value) LIKE ?', ['%'.$search.'%']);

                            $billingFilters = [
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
                            ];
                            $profiles = Profile::whereFields($billingFilters, TRUE)->pluck('id')->all();

                            $innerQb->orWhereIn('billing_profile_id', $profiles);
                        });
                    }elseif($searchKey == 'shipping') {
                        $qb->where(function($innerQb) use ($search){
                            $innerQb->whereRaw('CONCAT_WS(" ", SFNAME.value, SLNAME.value) LIKE ?', ['%'.$search.'%']);

                            $shippingFilters = [
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
                            ];
                            $profiles = Profile::whereFields($shippingFilters, TRUE)->pluck('id')->all();

                            $innerQb->orWhereIn('shipping_profile_id', $profiles);
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

        $paymentMethodOptions = [];
        foreach(PaymentMethod::getPaymentMethods(['show_all_active' => TRUE], PaymentMethod::LOCATION_BACKOFFICE) as $paymentMethod){
            $paymentMethodOptions[$paymentMethod->id] = $paymentMethod->name;
        }

        return view('backend.order.index', [
            'stickyProducts' => $stickyProducts,
            'paymentMethodOptions' => $paymentMethodOptions
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

            $orderAction .= FormFacade::open(['route' => ['backend.sales.order.delete', 'id' => $order->id, 'backUrl' => RequestFacade::fullUrl()], 'class' => 'form-in-btn-group']);
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
                $orderAction .= '<div class="btn-group btn-group-xs"><button type="button" class="btn btn-default hold-on-click dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" aria-expanded="true"><i class="fa fa-flag-o"></i></button><ul class="dropdown-menu" role="menu">'.$processActions.'</ul></div>';
            }

            $printActions = '';
            if(Gate::allows('access', ['print_invoice']) && $order->isPrintable):
                $printActions .= '<li><a href="' . route('backend.sales.order.print', ['id' => $order->id]) . '" target="_blank">Invoice</a></li>';
            endif;

            if(Gate::allows('access', ['view_delivery_order']) && $order->isPrintable && ProjectHelper::isFeatureEnabled('order.print.packaging_slip', false)){
                $printActions .= '<li><a href="' . route('backend.sales.order.print', ['id' => $order->id, 'type' => 'packaging_slip']) . '" target="_blank">Packaging Slip</a></li>';
            }

            if(!empty($printActions)){
                $orderAction .= '<div class="btn-group btn-group-xs"><button type="button" class="btn btn-default hold-on-click dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" aria-expanded="true"><i class="fa fa-print"></i></button><ul class="dropdown-menu" role="menu">'.$printActions.'</ul></div>';
            }

            $resendActions = '';

            if(Gate::allows('access', ['resend_order_email'])){
                if(in_array($order->status, [Order::STATUS_PENDING])){
                    $resendActions .= '<li><a class="modal-ajax" href="'.route('backend.sales.order.resend_email', ['process' => 'confirmation', 'backUrl' => RequestFacade::fullUrl(), 'id' => $order->id]).'" target="_blank">Confirmation</a></li>';
                }

                if(in_array($order->status, [Order::STATUS_PROCESSING])){
                    $resendActions .= '<li><a class="modal-ajax" href="'.route('backend.sales.order.resend_email', ['process' => 'processing', 'backUrl' => RequestFacade::fullUrl(), 'id' => $order->id]).'" target="_blank">Processing</a></li>';
                }

                if(in_array($order->status, [Order::STATUS_COMPLETED])){
                    $resendActions .= '<li><a class="modal-ajax" href="'.route('backend.sales.order.resend_email', ['process' => 'completed', 'backUrl' => RequestFacade::fullUrl(), 'id' => $order->id]).'" target="_blank">Completed</a></li>';
                }

                if(in_array($order->status, [Order::STATUS_CANCELLED])){
                    $resendActions .= '<li><a class="modal-ajax" href="'.route('backend.sales.order.resend_email', ['process' => 'cancelled', 'backUrl' => RequestFacade::fullUrl(), 'id' => $order->id]).'" target="_blank">Cancelled</a></li>';
                }
            }

            if(!empty($resendActions)){
                $orderAction .= '<div class="btn-group btn-group-xs"><button type="button" class="btn btn-default hold-on-click dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" aria-expanded="true"><i class="fa fa-envelope-o"></i></button><ul class="dropdown-menu" role="menu">'.$resendActions.'</ul></div>';
            }

            $rowMeat = [
                '<input type="checkbox" name="id[]" value="'.$order->id.'" />',
                $orderAction,
                $idx + 1 + $orderingStart,
                $order->reference,
                $order->checkout_at?$order->checkout_at->format('d M Y, H:i'):''
            ];

            if(config('project.enable_delivery_date', false)){
                $rowMeat[] = $order->delivery_date?$order->delivery_date->format('d M Y'):'';
            }

            $rowMeat = array_merge($rowMeat, [
                $order->billing_full_name.'<div class="expanded-detail">'.$order->billingInformation->email.'<br/>'.$order->billingInformation->phone_number.'<br/>'.AddressHelper::printAddress($order->billingInformation->getDetails()).'</div>',
                $order->shipping_full_name.'<div class="expanded-detail">'.$order->shippingInformation->email.'<br/>'.$order->shippingInformation->phone_number.'<br/>'.AddressHelper::printAddress($order->billingInformation->getDetails()).'</div>',
            ]);

            foreach($stickyProducts as $stickyProduct){
                $rowMeat[] = $order->getProductQuantity($stickyProduct->id);
            }

            $orderTotal = PriceFormatter::formatNumber($order->total, $order->currency).'<div class="expanded-detail" data-ajax_load="'.route('backend.sales.order.quick_content_view', ['id' => $order->id]).'"></div>';
            $rowMeat = array_merge($rowMeat, [$orderTotal]);

            if(Gate::allows('access', ['view_payment'])):
                $rowMeat[] = '<span id="order-'.$order->id.'-payment-method">'.$order->paymentMethod->name.'</span>'.(!$order->isFinal?'<a class="btn btn-default btn-xs" data-inline_update_target="order-'.$order->id.'-payment-method" data-inline_update="'.route('backend.sales.order.quick_update', ['id' => $order->id, 'type' => 'payment_method']).'"><i class="fa fa-pencil"></i></a>':'');
                $outstanding = '<label class="label label-sm label-'.($order->outstanding > 0?'warning':'success').'">'.PriceFormatter::formatNumber($order->outstanding).'</label>';
                if($order->payments->count() > 0){
                    $outstanding .= '<div class="expanded-detail" data-ajax_load="'.route('backend.sales.order.quick_payment_view', ['id' => $order->id]).'"></div>';
                }
                $rowMeat[] = $outstanding;
            endif;

            $rowMeat = array_merge($rowMeat, ['<label class="label label-sm bg-'.OrderHelper::getOrderStatusLabelClass($order->status).' bg-font-'.OrderHelper::getOrderStatusLabelClass($order->status).'">'.Order::getStatusOptions($order->status, TRUE).'</label>']);

            if(Auth::user()->manageMultipleStores){
                $rowMeat = array_merge($rowMeat, [$order->store->name]);
            }

            $meat[] = $rowMeat;
        }

        return $meat;
    }

    public function create()
    {
        $order = new Order();
        $invoice = new Invoice();

        $paymentMethods = PaymentMethod::getPaymentMethods([
            'order' => $order,
            'show_all_active' => TRUE
        ], PaymentMethod::LOCATION_BACKOFFICE);

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

        $dueDatePresetOptions = $this->getDueDatePresetOptions();

        $invoiceDefaultPreset = ProjectHelper::getConfig('invoice_options.default_due_date_preset', null);

        return view('backend.order.create', [
            'order' => $order,
            'invoice' => $invoice,
            'lineItems' => $lineItems,
            'taxes' => isset($taxes)?$taxes:[],
            'cartPriceRules' => isset($cartPriceRules)?$cartPriceRules:[],
            'paymentMethodOptions' => $paymentMethodOptions,
            'invoiceDefaultDueDate' => null,
            'dueDatePresetOptions' => $dueDatePresetOptions,
            'invoiceDefaultPreset' => $invoiceDefaultPreset
        ]);
    }

    public function store(OrderFormRequest $request)
    {
        $order = new Order();
        $originalStatus = null;

        $customer = Customer::getByEmail($request->input('profile.email'));

        $order->notes = $request->input('notes');
        $order->delivery_date = $request->input('delivery_date', null);
        $order->payment_method_id = $request->input('payment_method', null);
        $order->currency = $request->input('currency');
        $order->conversion_rate = 1;
        $order->reference = microtime(true);
        if($request->has('additional_fields')){
            $order->additional_fields = $request->input('additional_fields');
        }

        $store = ProjectHelper::getStoreByRequest($request);
        $order->store()->associate($store);

        $order->save();
        $order->update([
            'reference' => $order->id
        ]);

        $order->saveProfile('billing', $request->input('profile'));
        $order->saveProfile('shipping', $request->input('shipping_profile'));

        $this->processConfigurations($request);

        //Use free form line items to pre-filled order Line items
        $order->setRelation('lineItems', OrderHelper::processLineItems($request, $order, true, true));

        //Based on filled line items, process order
        OrderHelper::processLineItems($request, $order);

        $order->load('lineItems');
        $order->calculateTotal();

        Event::fire(new OrderEvent('before_update_order', $order));

        Event::fire(new OrderEvent('process_payment', $order));

        if($request->input('action') == 'place_order'){
            $order->processStocks();
            $this->placeOrder($order);

            $profileData = $request->input('profile');

            $customer = Customer::saveCustomer($profileData, null, false);
        }else{
            $order->status = Order::STATUS_ADMIN_CART;
        }

        if($customer){
            $order->customer()->associate($customer);
        }

        $order->save();

        if($request->input('action') == 'place_order'){
            Event::fire(new OrderEvent('internal_place_order', $order));
        }

        // Save order due date after internal_place_order because Invoice is created after this event
        $invoice = $order->invoices->first();

        if ($invoice) {
            foreach ($request->input('invoices', []) as $invoiceId => $invoiceData) {
                if ($invoiceId == 0) {
                    if ($invoiceData['preset'] == 'custom' && trim($invoiceData['due_date'])) {
                        $dueDate = $invoiceData['due_date'] . ' 00:00:00';
                    } else {
                        $dueDate = $this->getDueDateByPreset($invoiceData['preset'], $request->input('delivery_date', null));
                    }

                    $invoice->update([
                        'due_date' => $dueDate
                    ]);
                }
            }
        }

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
            'internalMemos' => $order->internalMemos,
            'externalMemos' => $order->externalMemos,
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

    public function quickPaymentView($id)
    {
        $order = Order::findOrFail($id);
        $payments = $order->payments;

        return view('backend.order.quick_payment_view', [
            'order' => $order,
            'payments' => $payments,
        ]);
    }

    /**
     * Show form and process quick order update
     *
     * @param Request $request
     * @param $id id of order
     * @param $type type of quick update to perform
     * @return Response
     */
    public function quickUpdate(Request $request, $id, $type)
    {
        $order = Order::findOrFail($id);

        if ($request->isMethod('GET')) {
            $options = [];

            switch($type) {
                case 'payment_method':
                    $paymentMethods = PaymentMethod::getPaymentMethods([
                        'order' => $order,
                        'show_all_active' => TRUE
                    ], PaymentMethod::LOCATION_BACKOFFICE);

                    $paymentMethodOptions = [];
                    foreach($paymentMethods as $paymentMethod){
                        $paymentMethodOptions[$paymentMethod->id] = $paymentMethod->name;
                    }

                    $options['paymentMethodOptions'] = $paymentMethodOptions;
                    break;
            }

            return view('backend.order.quick_update.form.default', array_merge([
                'order' => $order,
                'type' => $type,
                'backUrl' => $request->input('backUrl', route('backend.sales.order.view', ['id' => $id]))
            ], $options));
        } else {
            switch($type) {
                case 'payment_method':
                    $paymentMethod = PaymentMethod::findOrFail($request->input('payment_method'));
                    $order->paymentMethod()->associate($paymentMethod);
                    $order->save();

                    return view('backend.order.quick_update.render.'.$type, [
                        'order' => $order,
                        'type' => $type
                    ]);
                    break;
            }
        }

        abort(400, 'Unspecified type');
    }

    public function printOrder(Request $request, $id, $type='invoice')
    {
        $user = $request->user();
        $order = Order::findOrFail($id);

        if($type == 'packaging_slip'){
            OrderHelper::saveOrderComment('Print Packaging Slip.', 'print_packaging_slip', $order, $user);

            if(config('project.print_format', config('kommercio.print_format')) == 'xls'){
                Excel::create('Packacing Slip #'.$order->reference, function($excel) use ($order) {
                    $excel->setDescription('Packacing Slip #'.$order->reference);
                    $excel->sheet('Sheet 1', function($sheet) use ($order, $excel){
                        $sheet->loadView(ProjectHelper::getViewTemplate('print.excel.order.packaging_slip'), [
                            'order' => $order,
                            'excel' => $excel
                        ]);
                    });
                })->download('xls');
            }

            return view(ProjectHelper::getViewTemplate('print.order.packaging_slip'), [
                'order' => $order
            ]);
        }

        OrderHelper::saveOrderComment('Print Invoice.', 'print_invoice', $order, $user);

        if(config('project.print_format', config('kommercio.print_format')) == 'xls'){
            Excel::create('Invoice #'.$order->reference, function($excel) use ($order) {
                $excel->setDescription('Invoice #'.$order->reference);
                $excel->sheet('Sheet 1', function($sheet) use ($order, $excel){
                    $sheet->loadView(ProjectHelper::getViewTemplate('print.excel.order.invoice'), [
                        'order' => $order,
                        'excel' => $excel
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
        $invoice = $order->invoices->first();

        $lineItems = old('line_items', $order->lineItems);

        $paymentMethods = PaymentMethod::getPaymentMethods([
            'order' => $order,
            'show_all_active' => TRUE
        ], PaymentMethod::LOCATION_BACKOFFICE);

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
            $oldValues['user_id'] = $order->customer && $order->customer->user?$order->customer->user->id:null;

            foreach($lineItems as $lineItem){
                $lineItemData = $lineItem->toArray();
                $lineItemTotal = $lineItem->calculateTotal();

                if($lineItem->isProduct){
                    $lineItemData['id'] = $lineItem->product->id;
                    $lineItemData['sku'] = $lineItem->product->sku;

                    $lineItemData['children'] = [];

                    foreach($lineItem->children as $lineItemChild){
                        if(!isset($lineItemData['children'][$lineItemChild->product_composite_id])){
                            $lineItemData['children'][$lineItemChild->product_composite_id] = [];
                        }

                        $lineItemChildData = $lineItemChild->toArray();
                        $lineItemChildData['id'] = $lineItemChild->product->id;
                        $lineItemChildData['sku'] = $lineItemChild->product->sku;
                        $lineItemChildData['product_configuration'] = [];

                        foreach($lineItemChild->productConfigurations as $productConfiguration){
                            $lineItemChildData['product_configuration'][$productConfiguration->id] = $productConfiguration->pivot->value;
                        }

                        $lineItemData['children'][$lineItemChild->product_composite_id][] = $lineItemChildData;
                    }
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

        $dueDatePresetOptions = $this->getDueDatePresetOptions();

        if (!empty($invoice->due_date)) {
            $invoiceDefaultPreset = $this->getDueDatePresetByDate($invoice->due_date, ProjectHelper::getConfig('enable_delivery_date', false) ? $order->delivery_date : null);
        } else {
            $invoiceDefaultPreset = null;
        }

        return view('backend.order.edit', [
            'order' => $order,
            'invoice' => $invoice,
            'lineItems' => $lineItems,
            'taxes' => isset($taxes)?$taxes:[],
            'cartPriceRules' => isset($cartPriceRules)?$cartPriceRules:[],
            'paymentMethodOptions' => $paymentMethodOptions,
            'editOrder' => $order->isCheckout?true:false,
            'invoiceDefaultDueDate' => $invoice ? $invoice->due_date : null,
            'dueDatePresetOptions' => $dueDatePresetOptions,
            'invoiceDefaultPreset' => $invoiceDefaultPreset
        ]);
    }

    public function update(OrderFormRequest $request, $id)
    {
        $order = Order::findOrFail($id);
        $originalStatus = $order->status;

        $customer = Customer::getByEmail($request->input('profile.email'));

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

        // Set original line items
        $order->originalLineItems = $order->lineItems;

        // Use free form line items
        $order->setRelation('lineItems', OrderHelper::processLineItems($request, $order, true, true));

        foreach ($request->input('invoices', []) as $invoiceId => $invoiceData) {
            $invoice = $order->invoices->filter(function($invoice) use ($invoiceId) {
                return $invoice->id == $invoiceId;
            })->first();

            if ($invoice) {
                if ($invoiceData['preset'] == 'custom' && trim($invoiceData['due_date'])) {
                    $dueDate = $invoiceData['due_date'] . ' 00:00:00';
                } else {
                    $dueDate = $this->getDueDateByPreset($invoiceData['preset'], $request->input('delivery_date', null));
                }

                $invoice->update([
                    'due_date' => $dueDate
                ]);
            }
        }

        // Process configurations HAS TO BE here because above OrderHelper::processLineItems is dummy
        $this->processConfigurations($request);

        OrderHelper::processLineItems($request, $order);

        $order->load('lineItems');
        $order->calculateTotal();

        Event::fire(new OrderEvent('before_update_order', $order));

        if($request->input('action') == 'place_order'){
            //If order is not cart, return all stocks first
            if(in_array($order->status, [Order::STATUS_CART, Order::STATUS_ADMIN_CART, Order::STATUS_CANCELLED])){
                $order->returnStocks();
            }

            $order->processStocks();
            $this->placeOrder($order);

            $profileData = $request->input('profile');

            $customer = Customer::saveCustomer($profileData, null, false);
        }else{

        }

        if($customer){
            $order->customer()->associate($customer);
        }

        $order->save();

        Event::fire(new OrderUpdate($order, $originalStatus, $request->input('send_notification')));

        if($request->input('action') == 'place_order'){
            Event::fire(new OrderEvent('internal_place_order', $order));
        }elseif($order->isCheckout){
            Event::fire(new OrderEvent('placed_order_updated', $order));
        }

        return redirect($request->input('backUrl', route('backend.sales.order.index')))->with('success', ['This '.Order::getStatusOptions($order->status, true).' order has successfully been updated.']);
    }

    public function delete(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $order->delete();

        return redirect($request->input('backUrl', route('backend.sales.order.index')))->with('success', ['This '.Order::getStatusOptions($order->status, true).' order has successfully been deleted.']);
    }

    public function deleteAll()
    {
        if(config('app.env') != 'production'){
            $orders = Order::all();

            foreach($orders as $order){
                $order->forceDelete();
            }

            return redirect()->route('backend.sales.order.index')->with('success', ['All orders have been deleted.']);
        }else{
            return redirect()->route('backend.sales.order.index')->withErrors(['What the heck!!']);
        }
    }

    public function process(Request $request, $process, $id=null, $internal = FALSE)
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

                    // Delivery order validation if from single form
                    $totalDelivered = 0;

                    if($request->has('line_items')){
                        $rules = [];
                        foreach($order->getProductLineItems() as $productLineItem){
                            $maxAllowed = $productLineItem->quantity - $productLineItem->deliveredQuantity;
                            $rules['line_items.'.$productLineItem->id.'.quantity'] = 'required|integer|min:0|max:'.$maxAllowed;
                        }

                        $this->validate($request, $rules);

                        $deliveredLineItems = $request->input('line_items', []);
                    }else{
                        $deliveredLineItems = [];

                        foreach($order->getProductLineItems() as $productLineItem){
                            $deliveredLineItems[$productLineItem->id] = [
                                'quantity' => $productLineItem->quantity - $productLineItem->shippedQuantity
                            ];
                        }
                    }

                    array_walk($deliveredLineItems, function($item, $key) use (&$totalDelivered){
                        $totalDelivered += $item['quantity'];
                    });

                    if(!$totalDelivered){
                        return redirect()->back()->withErrors(['Delivered quantity must be higher than 0.']);
                    }

                    $deliveryOrderData = [
                        'notes' => $request->input('notes', null),
                        'shippingProfile' => $order->shippingInformation->getDetails(),
                        'data' => []
                    ];

                    if(!empty($request->input('tracking_number'))){
                        $deliveryOrderData['data']['tracking_number'] = $request->input('tracking_number');
                    }

                    if(!empty($request->input('delivered_by'))){
                        $deliveryOrderData['data']['delivered_by'] = $request->input('delivered_by');
                    }

                    $deliveryOrder = $order->createDeliveryOrder($deliveredLineItems, $deliveryOrderData);
                    Event::fire(new DeliveryOrderEvent(DeliveryOrderEvent::ON_NEW_DELIVERY_ORDER, $deliveryOrder));

                    if ($request->input('mark_shipped')) {
                        $deliveryOrder->changeStatus(DeliveryOrder::STATUS_SHIPPED, $request->input('send_notification'));
                    }

                    if($order->isFullyShipped){
                        OrderHelper::saveOrderComment('Delivery Order #'.$deliveryOrder->reference.' is created. Order is fully shipped.', 'fully_shipped', $order, $user);
                        OrderHelper::saveOrderComment('Order is fully shipped.', 'fully_shipped', $order, $user, OrderComment::TYPE_EXTERNAL_MEMO, [
                            'delivery_order_id' => $deliveryOrder->id
                        ]);

                        $order->status = Order::STATUS_SHIPPED;
                        $message = 'Order has been <span class="label bg-'.OrderHelper::getOrderStatusLabelClass($order->status).' bg-font-'.OrderHelper::getOrderStatusLabelClass($order->status).'">fully shipped.</span>';
                    }else{
                        OrderHelper::saveOrderComment('Delivery Order #'.$deliveryOrder->reference.' is created. Order is partially shipped.', 'partially_shipped', $order, $user);
                        OrderHelper::saveOrderComment('Order is partially shipped.', 'partially_shipped', $order, $user, OrderComment::TYPE_EXTERNAL_MEMO, [
                            'delivery_order_id' => $deliveryOrder->id
                        ]);
                        $message = 'Order has been partially shipped with Delivery Order <span class="label bg-'.OrderHelper::getOrderStatusLabelClass($order->status).' bg-font-'.OrderHelper::getOrderStatusLabelClass($order->status).'">#'.$deliveryOrder->reference.'.</span>';
                    }
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
            Event::fire(new OrderEvent('internal_place_order', $order));

            if(!$internal){
                return redirect($request->input('backUrl', route('backend.sales.order.index')))->with('success', [$message]);
            }else{
                return $order;
            }
        }
    }

    public function bulkProcess(Request $request, $process)
    {
        $processedOrders = [];
        $unprocessedOrders = [];
        $message = '';
        $selectedOrderCount = count($request->input('order_id', []));

        switch($process){
            case 'processing':
                if($request->input('confirm') == '1'){
                    $processedCount = 0;

                    foreach($request->input('order_id', []) as $order_id){
                        if($this->process($request, $process, $order_id, TRUE)){
                            $processedCount += 1;
                        }
                    }

                    $message = $processedCount.' '.str_plural('Order', $processedCount).' successfully set to <span class="label bg-'.OrderHelper::getOrderStatusLabelClass(Order::STATUS_PROCESSING).' bg-font-'.OrderHelper::getOrderStatusLabelClass(Order::STATUS_PROCESSING).'">'.Order::getStatusOptions(Order::STATUS_PROCESSING).'.</span>';
                }else{
                    foreach($request->input('id') as $order_id){
                        $order = Order::findOrFail($order_id);
                        if($order->isProcessable){
                            $processedOrders[] = $order;
                        }else{
                            $unprocessedOrders[] = $order;
                        }
                    }
                }

                $processForm = 'processing_form';
                break;
            case 'shipped':
                if($request->input('confirm') == '1'){
                    $processedCount = 0;

                    foreach($request->input('order_id', []) as $order_id){
                        if($this->process($request, $process, $order_id, TRUE)){
                            $processedCount += 1;
                        }
                    }

                    $message = $processedCount.' '.str_plural('Order', $processedCount).' successfully set to <span class="label bg-'.OrderHelper::getOrderStatusLabelClass(Order::STATUS_SHIPPED).' bg-font-'.OrderHelper::getOrderStatusLabelClass(Order::STATUS_SHIPPED).'">'.Order::getStatusOptions(Order::STATUS_SHIPPED).'.</span>';
                }else{
                    foreach($request->input('id') as $order_id){
                        $order = Order::findOrFail($order_id);
                        if($order->isShippable){
                            $processedOrders[] = $order;
                        }else{
                            $unprocessedOrders[] = $order;
                        }
                    }
                }

                $processForm = 'shipped_form';
                break;
            case 'completed':
                if($request->input('confirm') == '1'){
                    $processedCount = 0;

                    foreach($request->input('order_id', []) as $order_id){
                        if($this->process($request, $process, $order_id, TRUE)){
                            $processedCount += 1;
                        }
                    }

                    $message = $processedCount.' '.str_plural('Order', $processedCount).' successfully set to <span class="label bg-'.OrderHelper::getOrderStatusLabelClass(Order::STATUS_COMPLETED).' bg-font-'.OrderHelper::getOrderStatusLabelClass(Order::STATUS_COMPLETED).'">'.Order::getStatusOptions(Order::STATUS_COMPLETED).'.</span>';
                }else{
                    foreach($request->input('id') as $order_id){
                        $order = Order::findOrFail($order_id);
                        if($order->isCompleteable){
                            $processedOrders[] = $order;
                        }else{
                            $unprocessedOrders[] = $order;
                        }
                    }
                }

                $processForm = 'completed_form';
                break;
            case 'cancelled':
                if($request->input('confirm') == '1'){
                    $processedCount = 0;

                    foreach($request->input('order_id', []) as $order_id){
                        if($this->process($request, $process, $order_id, TRUE)){
                            $processedCount += 1;
                        }
                    }

                    $message = $processedCount.' '.str_plural('Order', $processedCount).' successfully set to <span class="label bg-'.OrderHelper::getOrderStatusLabelClass(Order::STATUS_SHIPPED).' bg-font-'.OrderHelper::getOrderStatusLabelClass(Order::STATUS_CANCELLED).'">'.Order::getStatusOptions(Order::STATUS_CANCELLED).'.</span>';
                }else{
                    foreach($request->input('id') as $order_id){
                        $order = Order::findOrFail($order_id);
                        if($order->isCancellable){
                            $processedOrders[] = $order;
                        }else{
                            $unprocessedOrders[] = $order;
                        }
                    }
                }

                $processForm = 'cancelled_form';
                break;
            default:
                return response('No process is selected.');
                break;
        }

        if($request->input('confirm') == '1'){
            return redirect($request->input('backUrl', route('backend.sales.order.index')))->with('success', [$message]);
        }else{
            if(count($processedOrders) > 0){
                return view('backend.order.process.bulk.'.$processForm, [
                    'processedOrders' => $processedOrders,
                    'unprocessedOrders' => $unprocessedOrders,
                    'backUrl' => $request->input('backUrl', route('backend.sales.order.index'))
                ]);
            }else{
                return response('Selected '.str_plural('Order', $selectedOrderCount).' can\'t be processed.', 422);
            }
        }
    }

    public function bulkAction(Request $request)
    {
        $arguments = explode(':', $request->input('action'));
        $action = array_shift($arguments);

        array_unshift($arguments, $request);

        $function = camel_case('bulk_'.$action);
        return call_user_func_array([$this, $function], $arguments);
    }

    public function resendEmail(Request $request, $process, $id)
    {
        $user = $request->user();
        $order = Order::findOrFail($id);

        if($request->isMethod('GET')){
            $options = [
                'process' => $process,
                'order' => $order,
                'backUrl' => $request->get('backUrl', route('backend.sales.order.index'))
            ];

            return view('backend.order.resend_email_confirmation', $options);
        }else{
            if(Order::processAndStatusMap($process) != $order->status){
                abort(400);
            }

            $rules = [
                'email' => 'required|email'
            ];
            $this->validate($request, $rules);

            switch($process){
                case 'confirmation':
                    $orderComment = 'Resend Confirmation email.';
                    break;
                case 'processing':
                    if(!Gate::allows('access', ['process_order'])){
                        abort(403);
                    }

                    $orderComment = 'Resend Processing email.';
                    break;
                case 'completed':
                    if(!Gate::allows('access', ['complete_order'])){
                        abort(403);
                    }

                    $orderComment = 'Resend Completed email.';
                    break;
                case 'cancelled':
                    if(!Gate::allows('access', ['cancel_order'])){
                        abort(403);
                    }

                    $orderComment = 'Resend Cancelled email.';
                    break;
                default:
                    return response('No process is selected.');
                    break;
            }

            OrderHelper::saveOrderComment($orderComment, $process, $order, $user);
            OrderHelper::sendOrderEmail($order, $process, $request->input('email'));

            $message = ucfirst($process).' email is successfully queued for resend.';

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

        if($request->input('isParent')){
            $render = view('backend.order.line_items.form.product', [
                'product' => $model,
                'key' => $request->get('product_index'),
                'order' => new Order()
            ])->render();
        }else{
            $parentProduct = Product::findOrFail($request->get('parent_product'));
            $render = view('backend.order.line_items.form.product_child', [
                'parent' => $parentProduct,
                'parentKey' => $request->get('parent_index'),
                'childKey' => $request->get('product_index'),
                'composite' => $parentProduct->getCompositeConfiguration((int) $request->get('composite')),
                'product' => $model,
                'order' => new Order()
            ])->render();
        }

        return response()->json(['data' => $render, '_token' => csrf_token()]);
    }

    public function shippingOptions(Request $request)
    {
        $return = [];

        $order = OrderHelper::createDummyOrderFromRequest($request);

        $shippingOptions = ShippingMethod::getShippingMethods([
            'order' => $order,
            'request' => $request,
            'frontend' => FALSE,
            'show_all_active' => TRUE
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
        $priceRules = OrderHelper::getCartRules($request, $order);

        if($request->ajax() || !$internal){
            $returnedData = [];

            foreach($priceRules as $priceRule){
                $returnedData[] = $priceRule->toArray() + ($priceRule->coupon?['coupon' => $priceRule->coupon->toArray()]:[]) + ['products' => array_keys($priceRule->getProducts())];
            }

            return response()->json([
                'data' => $returnedData,
                '_token' => csrf_token()
            ]);
        }else{
            return $priceRules;
        }
    }

    public function getCategoryAvailability(Request $request)
    {
        $store = ProjectHelper::getStoreByRequest($request);
        $store_id = $store->id;

        $perOrderLimits = OrderLimit::getOrderLimits([
            'limit_type' => OrderLimit::LIMIT_PER_ORDER,
            'store' => $store_id,
            'type' => OrderLimit::TYPE_PRODUCT_CATEGORY,
            'backoffice' => TRUE
        ]);

        if($request->has('delivery_date')){
            $deliveryOrderLimits = OrderLimit::getOrderLimits([
                'limit_type' => OrderLimit::LIMIT_DELIVERY_DATE,
                'date' => Carbon::createFromFormat('Y-m-d', $request->input('delivery_date')),
                'store' => $store_id,
                'type' => OrderLimit::TYPE_PRODUCT_CATEGORY,
                'backoffice' => TRUE
            ]);
        }else{
            $deliveryOrderLimits = [];
        }

        $todayOrderLimits = OrderLimit::getOrderLimits([
            'limit_type' => OrderLimit::LIMIT_ORDER_DATE,
            'date' => Carbon::now(),
            'store' => $store_id,
            'type' => OrderLimit::TYPE_PRODUCT_CATEGORY,
            'backoffice' => TRUE
        ]);

        $orderLimits = array_merge($perOrderLimits, $deliveryOrderLimits, $todayOrderLimits);

        $return = [];

        foreach($orderLimits as $orderLimit){
            $return[] = [
                'label' => implode(',', $orderLimit->productCategories->pluck('name')->all()),
                'limit' => floatval($orderLimit->limit),
                'limit_type' => $orderLimit->limit_type,
                'productCategories' => $orderLimit->productCategories->pluck('id')->all()
            ];
        }

        return new JsonResponse($return);
    }

    public function addCoupon(Request $request)
    {
        $couponCode = $request->input('coupon_code', 'ERRORCOUPON');

        $couponPriceRules = CartPriceRule::getCoupon($couponCode, null, $request);

        if(is_string($couponPriceRules)){
            return new JsonResponse([
                'coupon_code' => [$couponPriceRules]
            ], 422);
        }

        $coupons = [];
        foreach($couponPriceRules as $couponPriceRule)
        {
            $coupons[] = $couponPriceRule->coupon;
        }

        if($request->ajax()){
            return response()->json([
                'data' => $coupons,
                '_token' => csrf_token()
            ]);
        }else{
            return $coupons;
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

        $this->throwCouponEvents($order);

        return $order;
    }

    protected function throwCouponEvents(Order $order)
    {
        foreach($order->getCouponLineItems() as $couponLineItem){
            if($couponLineItem->coupon){
                Event::fire(new CouponEvent('used', $couponLineItem->coupon));
            }
        }
    }

    protected function processFinalPayment(Order $order, PaymentMethod $paymentMethod, Request $request)
    {
        return $paymentMethod->getProcessor()->finalProcessPayment([
            'order' => $order,
            'request' => $request
        ]);
    }

    protected function processConfigurations(Request $request)
    {
        $attributes = $request->all();

        foreach($attributes['line_items'] as $idx => &$lineItem){
            if(isset($lineItem['children'])){
                foreach($lineItem['children'] as $compositeId => $compositeProducts){
                    foreach($compositeProducts as $compositeIdx => $compositeProduct){
                        if(isset($compositeProduct['product_configuration'])){
                            $productId = $lineItem['children'][$compositeId][$compositeIdx]['line_item_id'];
                            $product = RuntimeCache::getOrSet('product_'.$productId, function() use ($productId){
                                return Product::findOrFail($productId);
                            });

                            $collectedConfigurations = [];

                            foreach($compositeProduct['product_configuration'] as $configurationId => $configuration){
                                $productConfiguration = $product->productConfigurationGroup->configurations->filter(function($row) use ($configurationId){
                                    return $row->id == $configurationId;
                                })->first();

                                if($productConfiguration){
                                    //Collect values
                                    $collectedConfigurations[$configurationId] = [
                                        'type' => $productConfiguration->type,
                                        'label' => $productConfiguration->name,
                                        'value' => $configuration
                                    ];
                                }
                            }

                            $lineItem['children'][$compositeId][$compositeIdx]['configurations'] = $collectedConfigurations;
                        }
                    }
                }
            }
        }

        $request->replace($attributes);
    }

    protected function getDueDatePresetOptions()
    {
        $options = array_merge(['0' => 'Not set'], ProjectHelper::getConfig('invoice_options.additional_due_date_presets', []), [
            'custom' => 'Custom'
        ]);

        return $options;
    }

    /**
     * Get due date preset value
     *
     * @param Carbon $dueDate
     * @param Carbon|null $deliveryDate If null, it will be ignored
     * @return string
     */
    protected function getDueDatePresetByDate(Carbon $dueDate, Carbon $deliveryDate = null)
    {
        if (empty($dueDate)) {
            return '0';
        }

        $dueDate->setTime(0, 0, 0);

        $comparisonDate = empty($comparisonDate) ? Carbon::now() : $comparisonDate;
        $comparisonDate->setTime(0, 0, 0);

        // Based on current date
        $dayDiff = $comparisonDate->diffInDays($dueDate, false);
        $dayDiff = $dayDiff >= 0 ? '+' . $dayDiff : $dayDiff;

        $weekDayDiff = $comparisonDate->diffInWeekdays($dueDate, false);
        $weekDayDiff = $weekDayDiff >= 0 ? '+' . $weekDayDiff : $weekDayDiff;

        $presetsByCurrentDate = [
            $dayDiff . 'd|current_date',
            $weekDayDiff . 'wd|current_date',
        ];

        // Based on delivery date
        if (!empty($deliveryDate)) {
            $deliveryDate->setTime(0, 0, 0);

            $dayDiff = $deliveryDate->diffInDays($dueDate, false);
            $dayDiff = $dayDiff >= 0 ? '+' . $dayDiff : $dayDiff;

            $weekDayDiff = $deliveryDate->diffInWeekdays($dueDate, false);
            $weekDayDiff = $weekDayDiff >= 0 ? '+' . $weekDayDiff : $weekDayDiff;

            $presetsByDeliveryDate = [
                $dayDiff . 'd|delivery_date',
                $weekDayDiff . 'wd|delivery_date',
            ];
        } else {
            $presetsByDeliveryDate = [];
        }

        $dueDatePresets = array_keys($this->getDueDatePresetOptions());

        foreach ($dueDatePresets as $dueDatePreset) {
            if (in_array($dueDatePreset, $presetsByCurrentDate, true) || in_array($dueDatePreset, $presetsByDeliveryDate, true)) {
                return $dueDatePreset;
                break;
            }
        }

        return 'custom';
    }

    /**
     * Parse preset and return due date
     * @param string $preset
     * @param Carbon|string|null $deliveryDate
     * @return Carbon|null
     */
    protected function getDueDateByPreset($preset, $deliveryDate = null)
    {
        if (in_array($preset, ['0', 0])) {
            return null;
        }

        $explodedParts = explode('|', $preset);
        $basedOn = $explodedParts[1];

        preg_match('/([+-])([0-9]+)([wd]{1,2})/', $explodedParts[0], $matches);
        $dayModifier = isset($matches[1]) ? $matches[1] : '+';
        $dayCount = isset($matches[2]) ? $matches[2] : 0;
        $dayType = isset($matches[3]) ? $matches[3] : 'd';
        $dayType = $dayType == 'wd' ? 'weekdays' : 'days';

        if ($basedOn == 'delivery_date' && !empty($deliveryDate)) {
            if (is_string($deliveryDate)) {
                $deliveryDate = new Carbon($deliveryDate);
                $deliveryDate->setTime(0, 0, 0);
            }

            $basedOnDate = $deliveryDate;
        } else if ($basedOn == 'current_date') {
            $basedOnDate = Carbon::now();
        } else {
            return null;
        }

        $modifier = $dayModifier . $dayCount . ' ' . $dayType;
        $basedOnDate->setTime(0, 0, 0)->modify($modifier);

        return $basedOnDate;
    }
}
