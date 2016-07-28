<?php

namespace Kommercio\Http\Controllers\Backend\Address;

use Illuminate\Http\Request;
use Kommercio\Facades\KommercioAPIHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Requests\Backend\Address\AddressFormRequest;
use Kommercio\Models\Address\Address;
use GuzzleHttp\Client;
use Kommercio\Models\Address\Area;
use Kommercio\Models\Address\City;
use Kommercio\Models\Address\Country;
use Kommercio\Models\Address\District;
use Kommercio\Models\Address\State;
use Kommercio\Models\ShippingMethod\ShippingMethod;

class AddressController extends Controller{
    protected $addressModel;
    protected $childType;
    protected $parentType;
    protected $parentModel;
    protected $parentObj;

    public function __construct(Request $request)
    {
        $type = $request->route('type');

        $this->addressClass = 'Kommercio\Models\Address\\'.ucfirst($type);

        $addressObj = with(new $this->addressClass);

        $this->parentType = $addressObj->parentType;
        $this->parentClass = 'Kommercio\Models\Address\\'.$addressObj->parentClass;
        $this->childType = $addressObj->childType;
        $this->childClass = 'Kommercio\Models\Address\\'.$addressObj->childClass;
        $this->parentObj = null;

        if($request->has('parent_id')){
            $this->parentObj = call_user_func(array($this->parentClass, 'findOrFail'), $request->get('parent_id'));
        }
    }

    public function index(Request $request, $type)
    {
        $qb = call_user_func(array($this->addressClass, 'query'))->orderBy('sort_order', 'ASC');

        if($request->has('parent_id')){
            $qb->where($this->parentType.'_id', $request->get('parent_id'));
        }

        $addresses = $qb->get();

        return view('backend.address.index', [
            'addresses' => $addresses,
            'parentObj' => $this->parentObj,
            'type' => $type,
            'pageTitle' => $this->parentObj?$this->parentObj->name:'Country'
        ]);
    }

    public function create(Request $request, $type)
    {
        $model = $this->addressClass;
        $address = new $model([
            'has_descendant' => TRUE,
            'active' => TRUE
        ]);

        $parentOptions = [];

        if($type == 'state'){
            $parentOptions = call_user_func(array($this->parentClass, 'all'))->pluck('name', 'id')->all();
        }elseif($type != 'country'){
            $parentOptions = call_user_func(array($this->parentClass, 'where'), $this->parentObj->parentType.'_id', $this->parentObj->getParent()->id)->get()->pluck('name', 'id')->all();
        }

        return view('backend.address.create', [
            'address' => $address,
            'type' => $type,
            'parentOptions' => $parentOptions,
            'parentObj' => $this->parentObj,
        ]);
    }

    public function store(AddressFormRequest $request, $type)
    {
        $model = $this->addressClass;
        $address = new $model;

        $address->fill($request->all());
        $address->setParent($request->input('parent_id'));
        $address->save();

        return redirect()->route('backend.configuration.address.index', ['type' => $type, 'parent_id' => $address->getParent()?$address->getParent()->id:''])->with('success', [$address->name.' has successfully been created.']);
    }

    public function edit($type, $id)
    {
        $model = $this->addressClass;
        $address = call_user_func([$model, 'findOrFail'], $id);

        $parentOptions = [];

        if($type == 'state'){
            $parentOptions = call_user_func(array($this->parentClass, 'all'))->pluck('name', 'id')->all();
        }elseif($type != 'country'){
            $parentOptions = call_user_func(array($this->parentClass, 'where'), $address->getParent()->parentType.'_id', $address->getParent()->getParent()->id)->get()->pluck('name', 'id')->all();
        }

        $address->parent_id = $address->getParent()?$address->getParent()->id:null;

        return view('backend.address.edit', [
            'address' => $address,
            'type' => $type,
            'parentOptions' => $parentOptions,
            'parentObj' => $this->parentObj,
        ]);
    }

    public function update(AddressFormRequest $request, $type, $id)
    {
        $model = $this->addressClass;
        $address = call_user_func([$model, 'findOrFail'], $id);

        $oldParentId = $address->getParent()?$address->getParent()->id:null;

        $address->fill($request->input());
        $address->setParent($request->input('parent_id'));
        $address->save();

        return redirect()->route('backend.configuration.address.index', ['type' => $type, 'parent_id' => $oldParentId])->with('success', [$address->name.' has successfully been updated.']);
    }

    public function delete(Request $request, $type, $id)
    {
        $model = $this->addressClass;
        $address = call_user_func([$model, 'findOrFail'], $id);

        if(!$this->isDeleteable($address)){
            return redirect()->back()->withErrors(['You can\'t delete '.$address->name.' because it contains children.']);
        }

        $name = $address->name;
        $address->delete();

        return redirect()->back()->with('success', [$name.' has successfully been deleted.']);
    }

    public function reorder(Request $request, $type)
    {
        foreach($request->input('objects') as $idx=>$object){
            $address = call_user_func(array($this->addressClass, 'findOrFail'), $object);
            $address->update([
                'sort_order' => $idx
            ]);
        }

        if($request->ajax()){
            return response()->json([
                'result' => 'success',
            ]);
        }else{
            return redirect()->route('backend.configuration.address.index', ['type' => $type]);
        }
    }

    public function isDeleteable(Address $address)
    {
        return count($address->getChildren()) < 1;
    }

    public function import(Request $request, $type, $id)
    {
        if($request->isMethod('GET')){
            $model = $this->addressClass;
            $address = call_user_func([$model, 'findOrFail'], $id);

            $parentOptions = [];

            if($type == 'state'){
                $parentOptions = call_user_func(array($this->parentClass, 'all'))->pluck('name', 'id')->all();
            }elseif($type != 'country'){
                $parentOptions = call_user_func(array($this->parentClass, 'where'), $address->getParent()->parentType.'_id', $address->getParent()->getParent()->id)->get()->pluck('name', 'id')->all();
            }

            $address->parent_id = $address->getParent()?$address->getParent()->id:null;

            return view('backend.address.import', [
                'address' => $address,
                'type' => $type,
                'parentOptions' => $parentOptions,
                'parentObj' => $this->parentObj,
            ]);
        }elseif($request->isMethod('POST')){
            $rules = [
                'import_url' => 'required'
            ];

            $this->validate($request, $rules);

            $client = new Client();
            $res = $client->get(KommercioAPIHelper::getAPIUrl().'/'.$request->input('import_url'), ['query' =>  ['api_token' => KommercioAPIHelper::getAPIToken()]]);

            if($res->getStatusCode() == 200){
                $model = $this->addressClass;
                $country = call_user_func([$model, 'findOrFail'], $id);

                $body = $res->getBody();

                $results = json_decode($body);

                foreach($results->states as $result){
                    $state = State::where('master_id', $result->id)->first();
                    if(!$state){
                        $state = new State();
                    }

                    foreach ($result as $key => $value) {
                        if($key == 'id'){
                            $state->master_id = $value;
                        }else{
                            $state->$key = $value;
                        }
                    }
                    $state->country_id = $country->id;
                    $state->save();
                }

                foreach($results->cities as $result){
                    $state = State::where('master_id', $result->state_id)->first();

                    if($state){
                        $city = City::where('master_id', $result->id)->first();
                        if(!$city){
                            $city = new City();
                        }

                        foreach ($result as $key => $value) {
                            if($key == 'id'){
                                $city->master_id = $value;
                            }else{
                                $city->$key = $value;
                            }
                        }
                        $city->state_id = $state->id;
                        $city->save();
                    }
                }

                foreach($results->districts as $result){
                    $city = City::where('master_id', $result->city_id)->first();

                    if($city){
                        $district = District::where('master_id', $result->id)->first();
                        if(!$district){
                            $district = new District();
                        }

                        foreach ($result as $key => $value) {
                            if($key == 'id'){
                                $district->master_id = $value;
                            }else{
                                $district->$key = $value;
                            }
                        }

                        $district->city_id = $city->id;
                        $district->save();
                    }
                }

                foreach($results->areas as $result){
                    $district = District::where('master_id', $result->district_id)->first();

                    if($district){
                        $area = Area::where('master_id', $result->id)->first();
                        if(!$area){
                            $area = new Area();
                        }

                        foreach ($result as $key => $value) {
                            if($key == 'id'){
                                $area->master_id = $value;
                            }else{
                                $area->$key = $value;
                            }
                        }

                        $area->district_id = $district->id;
                        $area->save();
                    }
                }
            }

            return redirect()->back()->with('success', ['Address is successfully imported.']);
        }
    }

    public function rates(Request $request, $type, $id)
    {
        $model = $this->addressClass;
        $address = call_user_func([$model, 'findOrFail'], $id);

        if($request->isMethod('GET')){
            $settingableShippingMethods = [];
            $shippingMethods = ShippingMethod::getShippingMethodObjects();

            foreach($shippingMethods as $shippingMethod){
                $shippingMethodImplements = class_implements($shippingMethod->getProcessor());
                if(in_array('Kommercio\ShippingMethods\ShippingMethodSettingsInterface', $shippingMethodImplements)){
                    $settingableShippingMethods[] = $shippingMethod;
                }
            }

            return view('backend.address.rates', [
                'address' => $address,
                'type' => $type,
                'settingableShippingMethods' => $settingableShippingMethods
            ]);
        }elseif($request->isMethod('POST')){
            $rules = [
                'shipping_method' => 'required|integer'
            ];

            $this->validate($request, $rules);

            $shippingMethod = ShippingMethod::findOrFail($request->input('shipping_method'));

            return $shippingMethod->getProcessor()->processSettings($request, $address);
        }
    }
}