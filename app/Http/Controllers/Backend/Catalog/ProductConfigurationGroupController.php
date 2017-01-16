<?php

namespace Kommercio\Http\Controllers\Backend\Catalog;

use Illuminate\Http\Request;
use Kommercio\Http\Requests\Backend\Catalog\ProductConfigurationGroupFormRequest;
use Kommercio\Http\Requests;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Product\Configuration\ProductConfigurationGroup;

class ProductConfigurationGroupController extends Controller
{
    public function index()
    {
        $productConfigurationGroups = ProductConfigurationGroup::all();

        return view('backend.catalog.product_configuration_group.index', [
            'productConfigurationGroups' => $productConfigurationGroups
        ]);
    }

    public function create()
    {
        $productConfigurationGroup = new ProductConfigurationGroup();

        return view('backend.catalog.product_configuration_group.create', [
            'productConfigurationGroup' => $productConfigurationGroup
        ]);
    }

    public function store(ProductConfigurationGroupFormRequest $request)
    {
        $productConfigurationGroup = new ProductConfigurationGroup($request->all());
        $productConfigurationGroup->save();

        return redirect($request->input('backUrl', route('backend.catalog.product_configuration.group.index')))->with('success', [$productConfigurationGroup->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $productConfigurationGroup = ProductConfigurationGroup::findOrFail($id);

        return view('backend.catalog.product_configuration_group.edit', [
            'productConfigurationGroup' => $productConfigurationGroup
        ]);
    }

    public function update(ProductConfigurationGroupFormRequest $request, $id)
    {
        $productConfigurationGroup = ProductConfigurationGroup::findOrFail($id);

        $productConfigurationGroup->fill($request->all());
        $productConfigurationGroup->save();

        return redirect($request->input('backUrl', route('backend.catalog.product_configuration.group.index')))->with('success', [$productConfigurationGroup->name.' has successfully been updated.']);
    }

    public function delete($id)
    {
        $productConfigurationGroup = ProductConfigurationGroup::findOrFail($id);

        //TODO: Check if used by settled Orders

        $name = $productConfigurationGroup->name;

        $productConfigurationGroup->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }
}
