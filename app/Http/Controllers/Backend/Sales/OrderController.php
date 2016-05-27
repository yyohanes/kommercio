<?php

namespace Kommercio\Http\Controllers\Backend\Sales;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Kommercio\Facades\OrderHelper;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Customer;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Order\LineItem;
use Kommercio\Http\Requests\Backend\Order\OrderFormRequest;
use Collective\Html\FormFacade;
use Illuminate\Support\Facades\Request as RequestFacade;
use Kommercio\Models\Product;
use Kommercio\Models\Profile\Profile;
use Kommercio\Facades\PriceFormatter;

class OrderController extends Controller{
    public function index(Request $request)
    {
        $qb = Order::joinBillingProfile()
            ->joinShippingProfile();

        if($request->ajax() || $request->wantsJson()){
            $totalRecords = $qb->count();

            foreach($request->input('filter', []) as $searchKey=>$search){
                if(trim($search) != ''){
                    if($searchKey == 'billing_full_name') {
                        $qb->whereRaw('CONCAT(BNAME.value, " ", BNAME.value) LIKE ?', ['%'.$search.'%']);
                    }elseif($searchKey == 'shipping_full_name') {
                        $qb->whereRaw('CONCAT(SNAME.value, " ", SNAME.value) LIKE ?', ['%'.$search.'%']);
                    }elseif($searchKey == 'reference'){
                        $qb->where($searchKey, 'LIKE', '%'.$search.'%');
                    }elseif($searchKey == 'checkout_at'){
                        if(!empty($search['from'])){
                            $qb->whereRaw('DATE_FORMAT(checkout_at, \'%Y-%m-%d\') <= ?', [$search['from']]);
                        }

                        if(!empty($search['to'])){
                            $qb->whereRaw('DATE_FORMAT(checkout_at, \'%Y-%m-%d\') >= ?', [$search['to']]);
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

        return view('backend.order.index');
    }

    protected function prepareDatatables($orders, $orderingStart=0)
    {
        $meat= [];

        foreach($orders as $idx=>$order){
            $orderAction = FormFacade::open(['route' => ['backend.sales.order.delete', 'id' => $order->id]]);
            $orderAction .= '<div class="btn-group btn-group-sm">';
            $orderAction .= '<a class="btn btn-default" href="'.route('backend.sales.order.edit', ['id' => $order->id, 'backUrl' => RequestFacade::fullUrl()]).'"><i class="fa fa-pencil"></i> Edit</a>';
            $orderAction .= '<button class="btn btn-default" data-toggle="confirmation" data-original-title="Are you sure?" title=""><i class="fa fa-trash-o"></i> Delete</button></div>';
            $orderAction .= FormFacade::close();

            $meat[] = [
                $idx + 1 + $orderingStart,
                $order->reference,
                $order->checkout_at?$order->checkout_at->format('d M Y, H:i'):'',
                $order->billing_full_name,
                $order->shipping_full_name,
                PriceFormatter::formatNumber($order->total),
                '<label class="label bg-'.OrderHelper::getOrderStatusLabelClass($order->status).'"><span class="bg-font-'.OrderHelper::getOrderStatusLabelClass($order->status).'">'.Order::getStatusOptions($order->status, TRUE).'</span></label>',
                $orderAction
            ];
        }

        return $meat;
    }

    public function create()
    {
        $order = new Order();

        $lineItems = old('line_items', []);

        return view('backend.order.create', [
            'order' => $order,
            'lineItems' => $lineItems
        ]);
    }

    public function store(OrderFormRequest $request)
    {
        $order = new Order();

        if($request->has('existing_customer')){
            $customer = Customer::whereField('email', $request->get('existing_customer'))->first();
            $order->customer()->associate($customer);
        }

        $order->store_id = $request->input('store_id');
        $order->currency = $request->input('currency');
        $order->conversion_rate = 1;
        $order->status = Order::STATUS_ADMIN_CART;
        $order->save();

        $order->saveProfile('billing', $request->input('profile'));
        $order->saveProfile('shipping', $request->input('shipping_profile'));

        $count = 0;
        foreach($request->input('line_items') as $lineItemDatum){
            if($lineItemDatum['line_item_type'] == 'product' && empty($lineItemDatum['quantity'])){
                continue;
            }

            $lineItem = new LineItem();
            $lineItem->order()->associate($order);
            $lineItem->processData($lineItemDatum, $count);
            $lineItem->save();

            $count += 1;
        }

        $order->load('lineItems');
        $order->calculateTotal();
        $order->save();

        return redirect()->route('backend.sales.order.index')->with('success', ['This '.Order::getStatusOptions($order->status, true).' order has successfully been created.']);
    }

    public function edit($id)
    {
        $order = Order::findOrFail($id);

        $lineItems = old('line_items', $order->lineItems);

        $oldValues = old();

        if(!$oldValues){
            $oldValues['existing_customer'] = $order->customer?$order->customer->getProfile()->email:null;
            $oldValues['profile'] = $order->billingProfile?$order->billingProfile->getDetails():[];
            $oldValues['shipping_profile'] = $order->shippingProfile?$order->shippingProfile->getDetails():[];

            foreach($lineItems as $lineItem){
                $lineItemData = $lineItem->toArray();
                if($lineItem->isProduct){
                    $lineItemData['sku'] = $lineItem->product->sku;
                }

                $lineItemData['lineitem_total_amount'] = $lineItem->calculateTotal();

                $oldValues['line_items'][] = $lineItemData;
            }

            Session::flashInput($oldValues);
        }

        return view('backend.order.edit', [
            'order' => $order,
            'lineItems' => $lineItems
        ]);
    }

    public function update(OrderFormRequest $request, $id)
    {
        $order = Order::findOrFail($id);

        if($request->has('existing_customer')){
            $customer = Customer::whereField('email', $request->get('existing_customer'))->first();
            $order->customer()->associate($customer);
        }

        $order->store_id = $request->input('store_id');
        $order->currency = $request->input('currency');
        $order->conversion_rate = 1;
        $order->status = Order::STATUS_ADMIN_CART;

        $order->saveProfile('billing', $request->input('profile'));
        $order->saveProfile('shipping', $request->input('shipping_profile'));

        $existingLineItems = $order->lineItems->all();
        $count = 0;

        foreach($request->input('line_items') as $lineItemDatum){
            if($lineItemDatum['line_item_type'] == 'product' && empty($lineItemDatum['quantity'])){
                continue;
            }

            if(!isset($existingLineItems[$count])){
                $lineItem = new LineItem();
                $lineItem->order()->associate($order);
            }else{
                //Clone and reset existing line item and will eventually be updated with new data
                $lineItem = $existingLineItems[$count];
                unset($existingLineItems[$count]);

                $lineItem->clearData();
            }

            $lineItem->processData($lineItemDatum, $count);
            $lineItem->save();

            $count += 1;
        }

        //Delete unused line items
        foreach($existingLineItems as $existingLineItem){
            $existingLineItem->delete();
        }

        $order->load('lineItems');
        $order->calculateTotal();
        $order->save();

        return redirect()->route('backend.sales.order.index')->with('success', ['This '.Order::getStatusOptions($order->status, true).' order has successfully been updated.']);
    }

    public function delete($id)
    {
        $order = Order::findOrFail($id);

        $order->forceDelete();

        return redirect()->route('backend.sales.order.index')->with('success', ['This '.Order::getStatusOptions($order->status, true).' order has successfully been deleted.']);
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
}