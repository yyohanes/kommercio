<?php

namespace Kommercio\Http\Controllers\Backend\Tax;

use Kommercio\Facades\AddressHelper;
use Kommercio\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kommercio\Http\Requests\Backend\Tax\TaxFormRequest;
use Kommercio\Models\Address\Country;
use Kommercio\Models\Tax;
use Kommercio\Models\Store;
use Kommercio\Facades\CurrencyHelper;

class TaxController extends Controller
{
    public function index()
    {
        $qb = Tax::with('countries')->orderBy('sort_order', 'ASC');
        $taxes = $qb->get();

        return view('backend.tax.index', [
            'taxes' => $taxes
        ]);
    }

    public function create()
    {
        $tax = new Tax();
        $tax->active = true;

        $currencyOptions = ['' => 'All Currencies'] + CurrencyHelper::getCurrencyOptions();

        $storeOptions = ['' => 'All Stores'] + Store::getStoreOptions();

        $country_id = old('country');

        $countryChildrenData = $this->getCountryChildrenData($country_id);

        return view('backend.tax.create', [
            'tax' => $tax,
            'currencyOptions' => $currencyOptions,
            'storeOptions' => $storeOptions,
        ] + $countryChildrenData);
    }

    public function store(TaxFormRequest $request)
    {
        $tax = new Tax();
        $tax->fill($request->all());
        $tax->save();

        if($request->has('country')){
            $tax->countries()->sync([$request->input('country', [])]);
            $tax->states()->sync($request->input('states', []));
            $tax->cities()->sync($request->input('cities', []));
            $tax->districts()->sync($request->input('districts', []));
            $tax->areas()->sync($request->input('areas', []));
        }else{
            $tax->countries()->detach();
            $tax->states()->detach();
            $tax->cities()->detach();
            $tax->districts()->detach();
            $tax->areas()->detach();
        }

        return redirect()->route('backend.tax.index')->with('success', [$tax->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $tax = Tax::findOrFail($id);

        $currencyOptions = ['' => 'All Currencies'] + CurrencyHelper::getCurrencyOptions();

        $storeOptions = ['' => 'All Stores'] + Store::getStoreOptions();

        $country_id = old('country', $tax->countries->get(0)?$tax->countries->get(0)->id:null);

        $countryChildrenData = $this->getCountryChildrenData($country_id);

        return view('backend.tax.edit', [
            'tax' => $tax,
            'currencyOptions' => $currencyOptions,
            'storeOptions' => $storeOptions,
        ] + $countryChildrenData);
    }

    public function update(TaxFormRequest $request, $id)
    {
        $tax = Tax::findOrFail($id);
        $tax->fill($request->all());
        $tax->save();

        if($request->has('country')){
            $tax->countries()->sync([$request->input('country', [])]);
            $tax->states()->sync($request->input('states', []));
            $tax->cities()->sync($request->input('cities', []));
            $tax->districts()->sync($request->input('districts', []));
            $tax->areas()->sync($request->input('areas', []));
        }else{
            $tax->countries()->detach();
            $tax->states()->detach();
            $tax->cities()->detach();
            $tax->districts()->detach();
            $tax->areas()->detach();
        }

        return redirect()->route('backend.tax.index')->with('success', [$tax->name.' has successfully been updated.']);
    }

    public function delete(Request $request, $id)
    {
        $tax = Tax::findOrFail($id);

        $tax->delete();

        $name = $tax->name;

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }

    public function reorder(Request $request)
    {
        foreach($request->input('objects') as $idx=>$object){
            $tax = Tax::findOrFail($object);
            $tax->update([
                'sort_order' => $idx
            ]);
        }

        if($request->ajax()){
            return response()->json([
                'result' => 'success',
            ]);
        }else{
            return redirect()->route('backend.tax.index');
        }
    }

    public function countryChildren($country_id=null)
    {
        $countryChildrenData = $this->getCountryChildrenData($country_id);

        return view('backend.tax.country_children', $countryChildrenData);
    }

    public function getCountryChildrenData($country_id)
    {
        AddressHelper::setAlwaysRefresh(TRUE);
        $countryOptions = ['' => 'All Countries'] + AddressHelper::getCountryOptions(FALSE);

        $stateOptions = AddressHelper::getStateOptions($country_id, FALSE);

        $allCities = [];
        $cityOptions = [];

        foreach($stateOptions as $stateId => $stateOption)
        {
            $cities = AddressHelper::getCityOptions($stateId, FALSE);
            $allCities += $cities;

            if($cities){
                $cityOptions[$stateOption] = $cities;
            }
        }

        $allDistricts = [];
        $districtOptions = [];

        foreach($allCities as $cityId => $city){
            $districts = AddressHelper::getDistrictOptions($cityId, FALSE);
            $allDistricts += $districts;

            if($districts){
                $districtOptions[$city] = $districts;
            }
        }

        $areaOptions = [];
        foreach($allDistricts as $districtId => $district){
            $areas = AddressHelper::getAreaOptions($districtId, FALSE);

            if($areas){
                $areaOptions[$district] = $areas;
            }
        }

        AddressHelper::setAlwaysRefresh(FALSE);

        return [
            'country_id' => $country_id,
            'countryOptions' => $countryOptions,
            'stateOptions' => $stateOptions,
            'cityOptions' => $cityOptions,
            'districtOptions' => $districtOptions,
            'areaOptions' => $areaOptions
        ];
    }
}