<?php

namespace Kommercio\Http\Controllers\Backend\Sales;

use Illuminate\Http\Request;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Http\Requests\Backend\Order\OrderLimitFormRequest;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Order\OrderLimit;
use Kommercio\Models\Order\OrderLimitDayRule;
use Kommercio\Models\Store;

class OrderLimitController extends Controller
{
    public function index($type)
    {
        $qb = OrderLimit::where('type', $type)->orderBy('sort_order', 'ASC');

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

        $dayRules = old('dayRules', [new OrderLimitDayRule()]);

        return view('backend.order.order_limits.create', [
            'orderLimit' => $orderLimit,
            'type' =>  $type,
            'defaultItems' => $defaultItems,
            'storeOptions' => $storeOptions,
            'dayRules' => $dayRules
        ]);
    }

    public function store(OrderLimitFormRequest $request, $type)
    {
        $orderLimit = new OrderLimit();
        $orderLimit->fill($request->all());
        $orderLimit->save();

        $days = array_keys(ProjectHelper::getDaysOptions());

        foreach($request->input('dayRules') as $idx => $dayRuleData){
            $dayRule = new OrderLimitDayRule();

            $selectedDays = $request->input('dayRules.'.$idx.'.days');

            foreach($days as $day){
                $dayRule->setAttribute($day, in_array($day, $selectedDays));
            }

            $orderLimit->dayRules()->save($dayRule);
        }

        $orderLimit->getItemRelation()->sync($request->input('items'));

        return redirect()->route('backend.order_limit.index', ['type' => $type])->with('success', [OrderLimit::getTypeOptions($type).' Order Limit has successfully been created.']);
    }

    public function edit($id)
    {
        $storeOptions = ['' => 'All Stores'] + Store::getStoreOptions();
        $orderLimit = OrderLimit::findOrFail($id);

        $type = $orderLimit->type;

        $defaultItems = [];
        foreach(old('items', $orderLimit->getItems()) as $item){
            $class = $this->getTypeClass($type);

            if(!is_object($item)){
                $itemObj = $class::findOrFail($item);
            }else{
                $itemObj = $item;
            }

            $defaultItems[$itemObj->id] = $itemObj->getName();
        }

        $dayRules = old('dayRules', $orderLimit->dayRules->all());

        if(count($dayRules) < 1){
            $dayRules = [new OrderLimitDayRule()];
        }

        return view('backend.order.order_limits.edit', [
            'orderLimit' => $orderLimit,
            'type' =>  $orderLimit->type,
            'defaultItems' => $defaultItems,
            'storeOptions' => $storeOptions,
            'dayRules' => $dayRules
        ]);
    }

    public function update(OrderLimitFormRequest $request, $id)
    {
        $orderLimit = OrderLimit::findOrFail($id);
        $orderLimit->fill($request->all());
        $orderLimit->save();

        $days = array_keys(ProjectHelper::getDaysOptions());

        foreach($request->input('dayRules') as $idx => $dayRuleData){
            $selectedDays = $request->input('dayRules.'.$idx.'.days');

            $dayRule = $orderLimit->dayRules->get($idx)?$orderLimit->dayRules->get($idx):new OrderLimitDayRule(['order_limit_id' => $orderLimit->id]);
            foreach($days as $day){
                $dayRule->setAttribute($day, in_array($day, $selectedDays));
            }

            $dayRule->save();
        }

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

    public function reorder(Request $request, $type)
    {
        foreach($request->input('objects') as $idx=>$object){
            $category = OrderLimit::findOrFail($object);
            $category->update([
                'sort_order' => $idx
            ]);
        }

        if($request->ajax()){
            return response()->json([
                'result' => 'success',
            ]);
        }else{
            return redirect()->route('backend.order_limit.index', ['type' => $type]);
        }
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
