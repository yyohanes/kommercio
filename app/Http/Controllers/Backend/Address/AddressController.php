<?php

namespace Kommercio\Http\Controllers\Backend\Address;

use Illuminate\Http\Request;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Requests\Backend\Address\AddressFormRequest;
use Kommercio\Models\Address\Address;

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
                'file' => 'required|file|mimes:zip'
            ];

            $this->validate($request, $rules);


        }
    }
}