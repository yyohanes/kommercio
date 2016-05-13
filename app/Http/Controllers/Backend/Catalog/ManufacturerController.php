<?php

namespace Kommercio\Http\Controllers\Backend\Catalog;

use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Requests\Backend\Catalog\ManufacturerFormRequest;
use Kommercio\Models\Manufacturer;

class ManufacturerController extends Controller{
    public function index()
    {
        $qb = Manufacturer::orderBy('created_at', 'DESC');

        $manufacturers = $qb->get();

        return view('backend.catalog.manufacturer.index', [
            'manufacturers' => $manufacturers,
        ]);
    }

    public function create()
    {
        $manufacturer = new Manufacturer();

        return view('backend.catalog.manufacturer.create', [
            'manufacturer' => $manufacturer,
        ]);
    }

    public function store(ManufacturerFormRequest $request)
    {
        $manufacturer = new Manufacturer();

        $manufacturer->fill($request->all());
        $manufacturer->save();

        if($request->has('logo')){
            foreach($request->input('logo', []) as $idx=>$image){
                $images[$image] = [
                    'type' => 'logo',
                ];
            }
            $manufacturer->attachMedia($images, 'logo');
        }

        return redirect()->route('backend.catalog.manufacturer.index')->with('success', [$manufacturer->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $manufacturer = Manufacturer::findOrFail($id);

        return view('backend.catalog.manufacturer.edit', [
            'manufacturer' => $manufacturer,
        ]);
    }

    public function update(ManufacturerFormRequest $request, $id)
    {
        $manufacturer = Manufacturer::findOrFail($id);

        $manufacturer->fill($request->all());
        $manufacturer->update();

        $images = [];
        foreach($request->input('logo', []) as $idx=>$image){
            $images[$image] = [
                'type' => 'logo',
            ];
        }

        $manufacturer->syncMedia($images, 'logo');

        return redirect($request->get('backUrl', route('backend.catalog.manufacturer.index')))->with('success', [$manufacturer->name.' has successfully been updated.']);
    }

    public function delete($id)
    {
        $manufacturer = Manufacturer::findOrFail($id);

        $name = $manufacturer->name;

        //Remove all media first. We do it manually because Translation model is cascaded, so we can't do this on Translation delete
        $manufacturer->deleteMedia('logo');

        $manufacturer->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }
}