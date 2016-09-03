<?php

namespace Kommercio\Http\Controllers\Backend\Catalog;

use Kommercio\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kommercio\Http\Requests\Backend\Catalog\ProductAttributeValueFormRequest;
use Kommercio\Models\ProductAttribute\ProductAttribute;
use Kommercio\Models\ProductAttribute\ProductAttributeValue;

class ProductAttributeValueController extends Controller{
    public function index(Request $request, $attribute_id)
    {
        $productAttribute = ProductAttribute::findOrFail($attribute_id);
        $productAttributeValues = $productAttribute->values;

        return view('backend.catalog.product_attribute_value.index', [
            'productAttributeValues' => $productAttributeValues,
            'productAttribute' => $productAttribute
        ]);
    }

    public function create($attribute_id)
    {
        $productAttribute = ProductAttribute::findOrFail($attribute_id);
        $productAttributeValue = new ProductAttributeValue();

        return view('backend.catalog.product_attribute_value.create', [
            'productAttribute' => $productAttribute,
            'productAttributeValue' => $productAttributeValue
        ]);
    }

    public function store(ProductAttributeValueFormRequest $request, $attribute_id)
    {
        $productAttribute = ProductAttribute::findOrFail($attribute_id);

        $productAttributeValue = new ProductAttributeValue();
        $productAttributeValue->fill($request->all());
        $productAttribute->values()->save($productAttributeValue);

        if($request->has('thumbnail')){
            foreach($request->input('thumbnail', []) as $idx=>$image){
                $thumbnail[$image] = [
                    'type' => 'thumbnail',
                    'caption' => $request->input('thumbnail_caption.'.$idx, null),
                ];
            }
            $productAttributeValue->attachMedia($thumbnail, 'thumbnail');
        }

        return redirect()->route('backend.catalog.product_attribute.value.index', ['attribute_id' => $attribute_id])->with('success', [$productAttributeValue->name.' has successfully been created.']);
    }

    public function edit($attribute_id, $id)
    {
        $productAttribute = ProductAttribute::findOrFail($attribute_id);
        $productAttributeValue = ProductAttributeValue::findOrFail($id);

        if($productAttributeValue->product_attribute_id != $attribute_id){
            abort(403, 'This value doesn\'t belong to this attribute.');
        }

        return view('backend.catalog.product_attribute_value.edit', [
            'productAttribute' => $productAttribute,
            'productAttributeValue' => $productAttributeValue
        ]);
    }

    public function update(ProductAttributeValueFormRequest $request, $attribute_id, $id)
    {
        $productAttributeValue = ProductAttributeValue::findOrFail($id);

        if($productAttributeValue->product_attribute_id != $attribute_id){
            abort(403, 'This value doesn\'t belong to this attribute.');
        }

        $productAttributeValue->fill($request->all());
        $productAttributeValue->save();

        $thumbnail = [];
        foreach($request->input('thumbnail', []) as $idx=>$image){
            $thumbnail[$image] = [
                'type' => 'thumbnail',
                'caption' => $request->input('thumbnail_caption.'.$idx, null),
            ];
        }
        $productAttributeValue->syncMedia($thumbnail, 'thumbnail');

        return redirect()->back()->with('success', [$productAttributeValue->name.' has successfully been updated.']);
    }

    public function delete($attribute_id, $id)
    {
        $productAttributeValue = ProductAttributeValue::findOrFail($id);

        if($productAttributeValue->product_attribute_id != $attribute_id){
            abort(403, 'This value doesn\'t belong to this attribute.');
        }

        if(!$this->isDeleteable($productAttributeValue)){
            return redirect()->back()->withErrors(['This value can\'t be deleted because it is used by other products.']);
        }

        $name = $productAttributeValue->name;

        $productAttributeValue->deleteMedia('thumbnail');
        $productAttributeValue->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }

    public function reorder(Request $request, $attribute_id)
    {
        $productAttribute = ProductAttribute::findOrFail($attribute_id);

        foreach($request->input('objects', []) as $idx=>$object){
            $productAttributeValue = ProductAttributeValue::findOrFail($object);
            $productAttributeValue->update([
                'sort_order' => $idx
            ]);
        }

        if($request->ajax()){
            return response()->json([
                'result' => 'success',
            ]);
        }else{
            return redirect()->route('backend.catalog.product_attribute.value.index', ['attribute_id' => $productAttribute->id]);
        }
    }

    public function isDeleteable(ProductAttributeValue $productAttributeValue)
    {
        return $productAttributeValue->products->count() < 1;
    }
}