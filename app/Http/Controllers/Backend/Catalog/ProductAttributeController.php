<?php

namespace Kommercio\Http\Controllers\Backend\Catalog;

use Kommercio\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kommercio\Http\Requests\Backend\Catalog\ProductAttributeFormRequest;
use Kommercio\Models\ProductAttribute\ProductAttribute;

class ProductAttributeController extends Controller{
    public function index()
    {
        $productAttributes = ProductAttribute::orderBy('sort_order', 'ASC')->get();

        return view('backend.catalog.product_attribute.index', [
            'productAttributes' => $productAttributes
        ]);
    }

    public function create()
    {
        $productAttribute = new ProductAttribute();

        return view('backend.catalog.product_attribute.create', [
            'productAttribute' => $productAttribute
        ]);
    }

    public function store(ProductAttributeFormRequest $request)
    {
        $productAttribute = new ProductAttribute();
        $productAttribute->fill($request->all());
        $productAttribute->save();

        return redirect()->route('backend.catalog.product_attribute.index')->with('success', [$productAttribute->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $productAttribute = ProductAttribute::findOrFail($id);

        return view('backend.catalog.product_attribute.edit', [
            'productAttribute' => $productAttribute
        ]);
    }

    public function update(ProductAttributeFormRequest $request, $id)
    {
        $productAttribute = ProductAttribute::findOrFail($id);
        $productAttribute->fill($request->all());
        $productAttribute->save();

        return redirect()->back()->with('success', [$productAttribute->name.' has successfully been updated.']);
    }

    public function delete($id)
    {
        $productAttribute = ProductAttribute::findOrFail($id);

        if(!$this->isDeleteable($productAttribute)){
            return redirect()->back()->withErrors(['This attribute can\'t be deleted because it is used by other products.']);
        }

        $name = $productAttribute->name;

        $productAttribute->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }

    public function reorder(Request $request)
    {
        foreach($request->input('objects') as $idx=>$object){
            $productAttribute = ProductAttribute::findOrFail($object);
            $productAttribute->update([
                'sort_order' => $idx
            ]);
        }

        if($request->ajax()){
            return response()->json([
                'result' => 'success',
            ]);
        }else{
            return redirect()->route('backend.catalog.product_attribute.index');
        }
    }

    public function isDeleteable(ProductAttribute $productAttribute)
    {
        return $productAttribute->products->count() < 1;
    }
}