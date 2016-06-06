<?php

namespace Kommercio\Http\Controllers\Backend\Sales;

use Kommercio\Http\Requests\Backend\Order\OrderLimitFormRequest;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Order\OrderLimit;
use Kommercio\Models\Store;

class OrderLimitController extends Controller
{
    public function index($type)
    {
        $qb = OrderLimit::where('type', $type);

        $orderLimits = $qb->get();

        return view('backend.order.order_limits.index', [
            'orderLimits' => $orderLimits,
            'type' =>  $type
        ]);
    }

    public function create($type)
    {
        $storeOptions = ['' => 'All Stores'] + Store::getStoreOptions();
        $orderLimit = new OrderLimit();

        $defaultItems = [];
        foreach(old('items', []) as $item){
            $class = $this->getTypeClass($type);
            $itemObj = $class::findOrFail($item);
            $defaultItems[$itemObj->id] = $itemObj->getName();
        }

        return view('backend.order.order_limits.create', [
            'orderLimit' => $orderLimit,
            'type' =>  $type,
            'defaultItems' => $defaultItems,
            'storeOptions' => $storeOptions
        ]);
    }

    public function store(OrderLimitFormRequest $request, $type)
    {
        $orderLimit = new OrderLimit();
        $orderLimit->fill($request->all());
        $orderLimit->save();

        $orderLimit->getItemRelation()->sync($request->input('items'));

        return redirect()->route('backend.order_limit.index', ['type' => $type])->with('success', [OrderLimit::getTypeOptions($type).' Order Limit has successfully been created.']);
    }

    public function edit($id)
    {
        $storeOptions = ['' => 'All Stores'] + Store::getStoreOptions();
        $orderLimit = OrderLimit::findOrFail($id);

        $defaultItems = [];
        foreach(old('items', $orderLimit->getItems()) as $item){
            $defaultItems[$item->id] = $item->getName();
        }

        return view('backend.order.order_limits.edit', [
            'orderLimit' => $orderLimit,
            'type' =>  $orderLimit->type,
            'defaultItems' => $defaultItems,
            'storeOptions' => $storeOptions
        ]);
    }

    public function update(OrderLimitFormRequest $request, $id)
    {
        $orderLimit = OrderLimit::findOrFail($id);
        $orderLimit->fill($request->all());
        $orderLimit->save();

        $orderLimit->getItemRelation()->sync($request->input('items'));

        return redirect($request->get('backUrl', route('backend.order_limit.index', ['type' => $orderLimit->type])))->with('success', [OrderLimit::getTypeOptions($orderLimit->type).' Order Limit has successfully been updated.']);
    }

    public function delete($id)
    {
        $orderLimit = OrderLimit::findOrFail($id);
        $orderLimit->getItemRelation()->detach();
        $orderLimit->delete();


        return redirect()->back()->with('success', [OrderLimit::getTypeOptions($orderLimit->type).' Order Limit has successfully been deleted.']);
    }

    protected function getTypeClass($type)
    {
        switch($type){
            case OrderLimit::TYPE_PRODUCT_CATEGORY:
                $return = '\Kommercio\Models\ProductCategory';
                break;
            default:
                $return = '\Kommercio\Models\Product';
                break;
        }

        return $return;
    }
}
