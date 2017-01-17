<?php

namespace Kommercio\Http\Controllers\Backend\Catalog;

use Illuminate\Http\Request;
use Kommercio\Http\Requests\Backend\Catalog\ProductConfigurationFormRequest;
use Kommercio\Http\Requests;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Product\Configuration\ProductConfiguration;
use Kommercio\Models\Product\Configuration\ProductConfigurationGroup;

class ProductConfigurationController extends Controller
{
    public function index($group_id)
    {
        $productConfigurationGroup = ProductConfigurationGroup::findOrFail($group_id);
        $configurations = $productConfigurationGroup->configurations;

        return view('backend.catalog.product_configuration.index', [
            'productConfigurationGroup' => $productConfigurationGroup,
            'configurations' => $configurations
        ]);
    }

    public function create($group_id)
    {
        $productConfigurationGroup = ProductConfigurationGroup::findOrFail($group_id);
        $configuration = new ProductConfiguration();

        $required = false;

        return view('backend.catalog.product_configuration.create', [
            'productConfigurationGroup' => $productConfigurationGroup,
            'configuration' => $configuration,
            'typeOptions' => ProductConfiguration::getTypeOptions(),
            'required' => $required,
            'label' => null
        ]);
    }

    public function store(ProductConfigurationFormRequest $request, $group_id)
    {
        $productConfigurationGroup = ProductConfigurationGroup::findOrFail($group_id);
        $configuration = new ProductConfiguration($request->all());
        $this->processByType($request, $configuration);
        $configuration->save();
        $configuration->groups()->attach($productConfigurationGroup, [
            'label' => $request->input('label'),
            'required' => $request->input('required', 0),
            'sort_order' => $productConfigurationGroup->configurations->last()?$productConfigurationGroup->configurations->last()->pivot->sort_order+1:0
        ]);

        return redirect($request->input('backUrl', route('backend.catalog.product_configuration.index', ['group_id' => $group_id])))->with('success', [$configuration->name.' has successfully been created.']);
    }

    public function edit($group_id, $id)
    {
        $productConfigurationGroup = ProductConfigurationGroup::findOrFail($group_id);

        if($check = $this->belongingCheck($productConfigurationGroup, $id)){
            return $check;
        }

        $configuration = $productConfigurationGroup->configurations->filter(function($configuration) use ($id){
            return $configuration->id == $id;
        })->first();

        $required = $configuration->pivot->required;

        return view('backend.catalog.product_configuration.edit', [
            'productConfigurationGroup' => $productConfigurationGroup,
            'configuration' => $configuration,
            'typeOptions' => ProductConfiguration::getTypeOptions(),
            'required' => $required,
            'label' => $configuration->pivot->label
        ]);
    }

    public function update(ProductConfigurationFormRequest $request, $group_id, $id)
    {
        $productConfigurationGroup = ProductConfigurationGroup::findOrFail($group_id);

        if($check = $this->belongingCheck($productConfigurationGroup, $id)){
            return $check;
        }

        $configuration = $productConfigurationGroup->configurations->filter(function($configuration) use ($id){
            return $configuration->id == $id;
        })->first();
        $configuration->fill($request->all());

        $this->processByType($request, $configuration);
        $configuration->save();

        $configuration->groups()->detach($productConfigurationGroup);
        $configuration->groups()->attach($productConfigurationGroup, [
            'label' => $request->input('label'),
            'required' => $request->input('required', 0),
            'sort_order' => $configuration->pivot->sort_order
        ]);

        return redirect($request->input('backUrl', route('backend.catalog.product_configuration.index', ['group_id' => $group_id])))->with('success', [$configuration->name.' has successfully been updated.']);
    }

    public function delete($group_id, $id)
    {
        $productConfigurationGroup = ProductConfigurationGroup::findOrFail($group_id);

        if($check = $this->belongingCheck($productConfigurationGroup, $id)){
            return $check;
        }

        $configuration = ProductConfiguration::findOrFail($id);

        //TODO: Check if used by settled Orders

        $name = $configuration->name;

        $configuration->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }

    public function reorder(Request $request, $group_id)
    {
        $productConfigurationGroup = ProductConfigurationGroup::findOrFail($group_id);

        foreach($request->input('objects', []) as $idx=>$object){
            $productConfigurationGroup->configurations()->updateExistingPivot($object, [
                'sort_order' => $idx
            ]);
        }

        if($request->ajax()){
            return response()->json([
                'result' => 'success',
            ]);
        }else{
            return redirect()->route('backend.catalog.product_configuration.index', ['group_id' => $group_id->id]);
        }
    }

    protected function processByType(ProductConfigurationFormRequest $request, $configuration)
    {
        if($request->has($request->input('type').'.rules')){
            $configuration->saveData([
                'rules' => $request->input($request->input('type').'.rules')
            ]);
        }else{
            $configuration->unsetData('rules');
        }
    }
    protected function belongingCheck(ProductConfigurationGroup $productConfigurationGroup, $id)
    {
        if(!$productConfigurationGroup->configurations->pluck('id')->contains($id)){
            return redirect()->back()->withErrors(['Configuration doesn\'t belong to this group.']);
        }

        return false;
    }
}
