<?php

namespace Kommercio\Http\Controllers\Backend\PaymentMethod;

use Illuminate\Http\Request;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Requests\Backend\PaymentMethod\PaymentMethodFormRequest;
use Kommercio\Models\PaymentMethod\PaymentMethod;

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
            'paymentMethod' => $paymentMethod
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

        return view('backend.payment_method.edit', [
            'paymentMethod' => $paymentMethod
        ]);
    }

    public function update(PaymentMethodFormRequest $request, $id)
    {
        $paymentMethod = PaymentMethod::findOrFail($id);

        $paymentMethod->fill($request->all());
        $paymentMethod->saveData($request->input('data', []));

        $paymentMethod->save();

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