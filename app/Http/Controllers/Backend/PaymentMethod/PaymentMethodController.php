<?php

namespace Kommercio\Http\Controllers\Backend\PaymentMethod;

use Illuminate\Http\Request;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Requests\Backend\PaymentMethod\PaymentMethodFormRequest;
use Kommercio\Models\PaymentMethod\PaymentMethod;
use Kommercio\Models\Store;
use Kommercio\Models\ShippingMethod\ShippingMethod;
use Kommercio\PaymentMethods\PaymentMethodSettingFormInterface;

class PaymentMethodController extends Controller{
    public function index()
    {
        $qb = PaymentMethod::orderBy('sort_order', 'ASC');

        $paymentMethods = $qb->get();

        return view('backend.payment_method.index', [
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function create()
    {
        $paymentMethod = new PaymentMethod();

        $shippingMethods = ShippingMethod::all();

        $stores = Store::all();

        $storeOptions = [];
        foreach($stores as $store){
            $type = strtoupper($store->type);

            if(!isset($type)){
                $storeOptions[$type] = [];
            }
            $storeOptions[$type][$store->id] = $store->name;
        }

        return view('backend.payment_method.create', [
            'paymentMethod' => $paymentMethod,
            'storeOptions' => $storeOptions,
            'shippingMethodOptions' => $shippingMethods->pluck('name', 'id')->all(),
            'additionalFieldsForm' => false
        ]);
    }

    public function store(PaymentMethodFormRequest $request)
    {
        $paymentMethod = new PaymentMethod();
        $paymentMethod->fill($request->all());
        $paymentMethod->save();

        $paymentMethod->shippingMethods()->sync($request->input('shipping_methods', []));

        if($request->input('store_scope') == 'selected'){
            $paymentMethod->stores()->sync($request->input('stores', []));
        }else{
            $paymentMethod->stores()->sync([]);
        }

        return redirect($request->get('backUrl', route('backend.payment_method.index')))->with('success', [$paymentMethod->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);

        $additionalFieldsForm = null;

        if($paymentMethod->getProcessor() instanceof PaymentMethodSettingFormInterface){
           $additionalFieldsForm = $paymentMethod->getProcessor()->settingForm();
        }

        $shippingMethods = ShippingMethod::all();

        $stores = Store::all();

        $storeOptions = [];
        foreach($stores as $store){
            $type = strtoupper($store->type);

            if(!isset($type)){
                $storeOptions[$type] = [];
            }
            $storeOptions[$type][$store->id] = $store->name;
        }

        return view('backend.payment_method.edit', [
            'paymentMethod' => $paymentMethod,
            'additionalFieldsForm' => $additionalFieldsForm,
            'storeOptions' => $storeOptions,
            'shippingMethodOptions' => $shippingMethods->pluck('name', 'id')->all(),
        ]);
    }

    public function update(PaymentMethodFormRequest $request, $id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);

        $paymentMethod->fill($request->all());

        if($request->has('data')){
            $paymentMethod->saveData($request->input('data', []));
        }

        $paymentMethod->save();

        $paymentMethod->shippingMethods()->sync($request->input('shipping_methods', []));

        if($request->input('store_scope') == 'selected'){
            $paymentMethod->stores()->sync($request->input('stores', []));
        }else{
            $paymentMethod->stores()->sync([]);
        }

        if($paymentMethod->getProcessor() instanceof PaymentMethodSettingFormInterface){
            $paymentMethod->getProcessor()->saveForm($request);
        }

        return redirect($request->get('backUrl', route('backend.payment_method.index')))->with('success', [$paymentMethod->name.' has successfully been updated.']);
    }

    public function delete($id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);
        $paymentMethod->stores()->sync([]);

        $name = $paymentMethod->name;

        $paymentMethod->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }

    public function reorder(Request $request)
    {
        foreach($request->input('objects') as $idx=>$object){
            $paymentMethod = PaymentMethod::findOrFail($object);
            $paymentMethod->update([
                'sort_order' => $idx
            ]);
        }

        if($request->ajax()){
            return response()->json([
                'result' => 'success',
            ]);
        }else{
            return redirect()->route('backend.payment_method.index');
        }
    }
}
