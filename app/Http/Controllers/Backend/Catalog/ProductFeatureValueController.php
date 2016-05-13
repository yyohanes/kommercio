<?php

namespace Kommercio\Http\Controllers\Backend\Catalog;

use Kommercio\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kommercio\Http\Requests\Backend\Catalog\ProductFeatureValueFormRequest;
use Kommercio\Models\ProductFeature\ProductFeature;
use Kommercio\Models\ProductFeature\ProductFeatureValue;

class ProductFeatureValueController extends Controller{
    public function index($feature_id)
    {
        $productFeature = ProductFeature::findOrFail($feature_id);
        $productFeatureValues = $productFeature->values;

        return view('backend.catalog.product_feature_value.index', [
            'productFeatureValues' => $productFeatureValues,
            'productFeature' => $productFeature
        ]);
    }

    public function create($feature_id)
    {
        $productFeature = ProductFeature::findOrFail($feature_id);
        $productFeatureValue = new ProductFeatureValue();

        return view('backend.catalog.product_feature_value.create', [
            'productFeature' => $productFeature,
            'productFeatureValue' => $productFeatureValue
        ]);
    }

    public function store(ProductFeatureValueFormRequest $request, $feature_id)
    {
        $productFeature = ProductFeature::findOrFail($feature_id);

        $productFeatureValue = new ProductFeatureValue();
        $productFeatureValue->fill($request->all());
        $productFeature->values()->save($productFeatureValue);

        return redirect()->route('backend.catalog.product_feature.value.index', ['feature_id' => $feature_id])->with('success', [$productFeatureValue->name.' has successfully been created.']);
    }

    public function edit($feature_id, $id)
    {
        $productFeature = ProductFeature::findOrFail($feature_id);
        $productFeatureValue = ProductFeatureValue::findOrFail($id);

        if($productFeatureValue->product_feature_id != $feature_id){
            abort(403, 'This value doesn\'t belong to this feature.');
        }

        return view('backend.catalog.product_feature_value.edit', [
            'productFeature' => $productFeature,
            'productFeatureValue' => $productFeatureValue
        ]);
    }

    public function update(ProductFeatureValueFormRequest $request, $feature_id, $id)
    {
        $productFeatureValue = ProductFeatureValue::findOrFail($id);

        if($productFeatureValue->product_feature_id != $feature_id){
            abort(403, 'This value doesn\'t belong to this feature.');
        }

        $productFeatureValue->fill($request->all());
        $productFeatureValue->save();

        return redirect()->back()->with('success', [$productFeatureValue->name.' has successfully been updated.']);
    }

    public function delete($feature_id, $id)
    {
        $productFeatureValue = ProductFeatureValue::findOrFail($id);

        if($productFeatureValue->product_feature_id != $feature_id){
            abort(403, 'This value doesn\'t belong to this feature.');
        }

        if(!$this->isDeleteable($productFeatureValue)){
            return redirect()->back()->withErrors(['This value can\'t be deleted because it is used by other products.']);
        }

        $name = $productFeatureValue->name;

        $productFeatureValue->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }

    public function reorder(Request $request, $feature_id)
    {
        $productFeature = ProductFeature::findOrFail($feature_id);

        foreach($request->input('objects', []) as $idx=>$object){
            $productFeatureValue = ProductFeatureValue::findOrFail($object);
            $productFeatureValue->update([
                'sort_order' => $idx
            ]);
        }

        if($request->ajax()){
            return response()->json([
                'result' => 'success',
            ]);
        }else{
            return redirect()->route('backend.catalog.product_feature.value.index', ['feature_id' => $productFeature->id]);
        }
    }

    public function isDeleteable(ProductFeatureValue $productFeatureValue)
    {
        return $productFeatureValue->products->count() < 1;
    }
}