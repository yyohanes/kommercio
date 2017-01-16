<?php

namespace Kommercio\Http\Controllers\Backend\Catalog;

use Illuminate\Http\Request;

use Kommercio\Http\Requests;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Requests\Backend\Catalog\ProductCompositeGroupFormRequest;
use Kommercio\Models\Product\Composite\ProductCompositeGroup;

class ProductCompositeGroupController extends Controller
{
    public function index()
    {
        $productCompositeGroups = ProductCompositeGroup::all();

        return view('backend.catalog.product_composite_group.index', [
            'productCompositeGroups' => $productCompositeGroups
        ]);
    }

    public function create()
    {
        $productCompositeGroup = new ProductCompositeGroup();

        return view('backend.catalog.product_composite_group.create', [
            'productCompositeGroup' => $productCompositeGroup
        ]);
    }

    public function store(ProductCompositeGroupFormRequest $request)
    {
        $productCompositeGroup = new ProductCompositeGroup($request->all());

        $productCompositeGroup->save();

        return redirect($request->input('backUrl', route('backend.catalog.product_composite.group.index')))->with('success', [$productCompositeGroup->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $productCompositeGroup = ProductCompositeGroup::findOrFail($id);

        return view('backend.catalog.product_composite_group.edit', [
            'productCompositeGroup' => $productCompositeGroup
        ]);
    }

    public function update(ProductCompositeGroupFormRequest $request, $id)
    {
        $productCompositeGroup = ProductCompositeGroup::findOrFail($id);

        $productCompositeGroup->fill($request->all());
        $productCompositeGroup->save();

        return redirect($request->input('backUrl', route('backend.catalog.product_composite.group.index')))->with('success', [$productCompositeGroup->name.' has successfully been updated.']);
    }

    public function delete($id)
    {
        $productCompositeGroup = ProductCompositeGroup::findOrFail($id);

        $name = $productCompositeGroup->name;

        $productCompositeGroup->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }
}
