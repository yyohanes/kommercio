<?php

namespace Kommercio\Http\Controllers\Backend\ShippingMethod;

use Illuminate\Http\Request;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Requests\Backend\ShippingMethod\ShippingMethodFormRequest;
use Kommercio\Models\PaymentMethod\PaymentMethod;
use Kommercio\Models\ShippingMethod\ShippingMethod;
use Kommercio\Models\Store;
use Kommercio\ShippingMethods\ShippingMethodSettingsInterface;

class ShippingMethodController extends Controller{
    public function index()
    {
        $qb = ShippingMethod::orderBy('sort_order', 'ASC');

        $shippingMethods = $qb->get();

        return view('backend.shipping_method.index', [
            'shippingMethods' => $shippingMethods,
        ]);
    }

    public function create()
    {
        $shippingMethod = new ShippingMethod();
        $stores = Store::all();
        $paymentMethods = PaymentMethod::all();

        $storeOptions = [];
        foreach($stores as $store){
            $type = strtoupper($store->type);

            if(!isset($type)){
                $storeOptions[$type] = [];
            }
            $storeOptions[$type][$store->id] = $store->name;
        }

        return view('backend.shipping_method.create', [
            'shippingMethod' => $shippingMethod,
            'storeOptions' => $storeOptions,
            'paymentMethodOptions' => $paymentMethods->pluck('name', 'id')->all(),
            'additionalFieldsForm' => false,
        ]);
    }

    public function store(ShippingMethodFormRequest $request)
    {
        $shippingMethod = new ShippingMethod();
        $shippingMethod->fill($request->all());
        $shippingMethod->save();

        $shippingMethod->paymentMethods()->sync($request->input('payment_methods', []));

        if($request->input('store_scope') == 'selected'){
            $shippingMethod->stores()->sync($request->input('stores', []));
        }else{
            $shippingMethod->stores()->sync([]);
        }

        return redirect($request->get('backUrl', route('backend.shipping_method.index')))->with('success', [$shippingMethod->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $shippingMethod = ShippingMethod::findOrFail($id);
        $stores = Store::all();
        $paymentMethods = PaymentMethod::all();

        $storeOptions = [];
        foreach($stores as $store){
            $type = strtoupper($store->type);

            if(!isset($type)){
                $storeOptions[$type] = [];
            }
            $storeOptions[$type][$store->id] = $store->name;
        }

        $additionalFieldsForm = null;

        if($shippingMethod->getProcessor() instanceof ShippingMethodSettingsInterface){
            $additionalFieldsForm = $shippingMethod->getProcessor()->renderAdditionalSetting();
        }

        return view('backend.shipping_method.edit', [
            'shippingMethod' => $shippingMethod,
            'storeOptions' => $storeOptions,
            'additionalFieldsForm' => $additionalFieldsForm,
            'paymentMethodOptions' => $paymentMethods->pluck('name', 'id')->all()
        ]);
    }

    public function update(ShippingMethodFormRequest $request, $id)
    {
        $shippingMethod = ShippingMethod::findOrFail($id);

        $shippingMethod->fill($request->all());

        if($request->has('data')){
            $shippingMethod->saveData($request->input('data', []));
        }

        $shippingMethod->save();

        $shippingMethod->paymentMethods()->sync($request->input('payment_methods', []));

        if($request->input('store_scope') == 'selected'){
            $shippingMethod->stores()->sync($request->input('stores', []));
        }else{
            $shippingMethod->stores()->sync([]);
        }

        if($shippingMethod->getProcessor() instanceof ShippingMethodSettingsInterface){
            $shippingMethod->getProcessor()->processAdditionalSetting($request);
        }

        return redirect($request->get('backUrl', route('backend.shipping_method.index')))->with('success', [$shippingMethod->name.' has successfully been updated.']);
    }

    public function delete($id)
    {
        $shippingMethod = ShippingMethod::findOrFail($id);

        $name = $shippingMethod->name;

        $shippingMethod->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }

    public function reorder(Request $request)
    {
        foreach($request->input('objects') as $idx=>$object){
            $shippingMethod = ShippingMethod::findOrFail($object);
            $shippingMethod->update([
                'sort_order' => $idx
            ]);
        }

        if($request->ajax()){
            return response()->json([
                'result' => 'success',
            ]);
        }else{
            return redirect()->route('backend.shipping_method.index');
        }
    }
}
