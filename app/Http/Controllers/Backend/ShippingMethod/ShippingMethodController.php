<?php

namespace Kommercio\Http\Controllers\Backend\ShippingMethod;

use Illuminate\Http\Request;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Requests\Backend\ShippingMethod\ShippingMethodFormRequest;
use Kommercio\Models\ShippingMethod\ShippingMethod;

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

        return view('backend.shipping_method.create', [
            'shippingMethod' => $shippingMethod
        ]);
    }

    public function store(ShippingMethodFormRequest $request)
    {
        $shippingMethod = new ShippingMethod();
        $shippingMethod->fill($request->all());
        $shippingMethod->save();

        return redirect($request->get('backUrl', route('backend.shipping_method.index')))->with('success', [$shippingMethod->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $shippingMethod = ShippingMethod::findOrFail($id);

        return view('backend.shipping_method.edit', [
            'shippingMethod' => $shippingMethod
        ]);
    }

    public function update(ShippingMethodFormRequest $request, $id)
    {
        $shippingMethod = ShippingMethod::findOrFail($id);

        $shippingMethod->fill($request->all());
        $shippingMethod->save();

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