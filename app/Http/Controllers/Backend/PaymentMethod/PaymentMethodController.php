<?php

namespace Kommercio\Http\Controllers\Backend\PaymentMethod;

use Illuminate\Http\Request;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Requests\Backend\PaymentMethod\PaymentMethodFormRequest;
use Kommercio\Models\PaymentMethod\PaymentMethod;
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

        return view('backend.payment_method.create', [
            'paymentMethod' => $paymentMethod,
            'additionalFieldsForm' => false
        ]);
    }

    public function store(PaymentMethodFormRequest $request)
    {
        $paymentMethod = new PaymentMethod();
        $paymentMethod->fill($request->all());
        $paymentMethod->save();

        return redirect($request->get('backUrl', route('backend.payment_method.index')))->with('success', [$paymentMethod->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);

        $additionalFieldsForm = null;

        if($paymentMethod->getProcessor() instanceof PaymentMethodSettingFormInterface){
           $additionalFieldsForm = ProjectHelper::getViewTemplate($paymentMethod->getProcessor()->settingForm());
        }

        return view('backend.payment_method.edit', [
            'paymentMethod' => $paymentMethod,
            'additionalFieldsForm' => $additionalFieldsForm
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

        if($paymentMethod->getProcessor() instanceof PaymentMethodSettingFormInterface){
            $paymentMethod->getProcessor()->saveForm($request);
        }

        return redirect($request->get('backUrl', route('backend.payment_method.index')))->with('success', [$paymentMethod->name.' has successfully been updated.']);
    }

    public function delete($id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);

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