<?php

namespace Kommercio\Http\Controllers\Backend\Catalog;

use Kommercio\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kommercio\Http\Requests\Backend\Catalog\ProductFeatureFormRequest;
use Kommercio\Models\ProductFeature\ProductFeature;

class ProductFeatureController extends Controller{
    public function index()
    {
        $productFeatures = ProductFeature::orderBy('sort_order', 'ASC')->get();

        return view('backend.catalog.product_feature.index', [
            'productFeatures' => $productFeatures
        ]);
    }

    public function create()
    {
        $productFeature = new ProductFeature();

        return view('backend.catalog.product_feature.create', [
            'productFeature' => $productFeature
        ]);
    }

    public function store(ProductFeatureFormRequest $request)
    {
        $productFeature = new ProductFeature();
        $productFeature->fill($request->all());
        $productFeature->save();

        return redirect()->route('backend.catalog.product_feature.index')->with('success', [$productFeature->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $productFeature = ProductFeature::findOrFail($id);

        return view('backend.catalog.product_feature.edit', [
            'productFeature' => $productFeature
        ]);
    }

    public function update(ProductFeatureFormRequest $request, $id)
    {
        $productFeature = ProductFeature::findOrFail($id);
        $productFeature->fill($request->all());
        $productFeature->save();

        return redirect()->back()->with('success', [$productFeature->name.' has successfully been updated.']);
    }

    public function delete($id)
    {
        $productFeature = ProductFeature::findOrFail($id);

        if(!$this->isDeleteable($productFeature)){
            return redirect()->back()->withErrors(['This feature can\'t be deleted because it is used by other products.']);
        }

        $name = $productFeature->name;

        $productFeature->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }

    public function reorder(Request $request)
    {
        foreach($request->input('objects') as $idx=>$object){
            $productFeature = ProductFeature::findOrFail($object);
            $productFeature->update([
                'sort_order' => $idx
            ]);
        }

        if($request->ajax()){
            return response()->json([
                'result' => 'success',
            ]);
        }else{
            return redirect()->route('backend.catalog.product_feature.index');
        }
    }

    public function isDeleteable(ProductFeature $productFeature)
    {
        return $productFeature->products->count() < 1;
    }
}