<?php

namespace Kommercio\Http\Controllers\Backend\Catalog;

use Illuminate\Http\Request;
use Kommercio\Http\Requests\Backend\Catalog\ProductCompositeFormRequest;
use Kommercio\Http\Requests;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Product\Composite\ProductComposite;
use Kommercio\Models\Product\Composite\ProductCompositeGroup;
use Kommercio\Models\ProductCategory;

class ProductCompositeController extends Controller
{
    public function index($group_id)
    {
        $productCompositeGroup = ProductCompositeGroup::findOrFail($group_id);
        $composites = $productCompositeGroup->composites;

        return view('backend.catalog.product_composite.index', [
            'productCompositeGroup' => $productCompositeGroup,
            'composites' => $composites,
        ]);
    }

    public function create($group_id)
    {
        $productCompositeGroup = ProductCompositeGroup::findOrFail($group_id);
        $composite = new ProductComposite();
        $productCategoryOptions = ProductCategory::getPossibleParentOptions();

        return view('backend.catalog.product_composite.create', [
            'productCompositeGroup' => $productCompositeGroup,
            'composite' => $composite,
            'productCategoryOptions' => $productCategoryOptions
        ]);
    }

    public function store(ProductCompositeFormRequest $request, $group_id)
    {
        $productCompositeGroup = ProductCompositeGroup::findOrFail($group_id);
        $composite = new ProductComposite($request->all());
        $composite->save();

        $productCompositeGroup->composites()->attach($composite);

        $this->processComposite($request, $composite);

        return redirect($request->input('backUrl', route('backend.catalog.product_composite.index', ['group_id' => $group_id])))->with('success', [$composite->name.' has successfully been created.']);
    }

    public function edit($group_id, $id)
    {
        $productCompositeGroup = ProductCompositeGroup::findOrFail($group_id);

        if($check = $this->belongingCheck($productCompositeGroup, $id)){
            return $check;
        }

        $composite = $productCompositeGroup->composites->filter(function($composite) use ($id){
            return $composite->id == $id;
        })->first();

        $productCategoryOptions = ProductCategory::getPossibleParentOptions();

        return view('backend.catalog.product_composite.edit', [
            'productCompositeGroup' => $productCompositeGroup,
            'composite' => $composite,
            'productCategoryOptions' => $productCategoryOptions
        ]);
    }

    public function update(ProductCompositeFormRequest $request, $group_id, $id)
    {
        $productCompositeGroup = ProductCompositeGroup::findOrFail($group_id);

        if($check = $this->belongingCheck($productCompositeGroup, $id)){
            return $check;
        }

        $composite = ProductComposite::findOrFail($id);
        $composite->fill($request->all());
        $composite->save();

        $composite->products()->detach();
        $this->processComposite($request, $composite);

        return redirect($request->input('backUrl', route('backend.catalog.product_composite.index', ['group_id' => $group_id])))->with('success', [$composite->name.' has successfully been updated.']);
    }

    public function delete($group_id, $id)
    {
        $productCompositeGroup = ProductCompositeGroup::findOrFail($group_id);

        if($check = $this->belongingCheck($productCompositeGroup, $id)){
            return $check;
        }

        $composite = ProductComposite::findOrFail($id);

        $name = $composite->name;

        $composite->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }

    public function reorder(Request $request, $group_id)
    {
        $productCompositeGroup = ProductCompositeGroup::findOrFail($group_id);

        foreach($request->input('objects', []) as $idx=>$object){
            $productCompositeGroup->composites()->updateExistingPivot($object, [
                'sort_order' => $idx
            ]);
        }

        if($request->ajax()){
            return response()->json([
                'result' => 'success',
            ]);
        }else{
            return redirect()->route('backend.catalog.product_composite.index', ['group_id' => $group_id->id]);
        }
    }

    protected function belongingCheck(ProductCompositeGroup $productCompositeGroup, $id)
    {
        if(!$productCompositeGroup->composites->pluck('id')->contains($id)){
            return redirect()->back()->withErrors(['Composite doesn\'t belong to this group.']);
        }

        return false;
    }

    protected function processComposite(ProductCompositeFormRequest $request, $composite, $update = false)
    {
        $configuredProductsData = [];

        foreach($request->input('composite_product', []) as $idx => $configuredProduct){
            $configuredProductsData[$configuredProduct] = [
                'sort_order' => $idx
            ];
        }

        $composite->products()->sync($configuredProductsData);

        $composite->productCategories()->sync($request->input('product_category', []));
    }
}
