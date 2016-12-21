<?php

namespace Kommercio\Http\Controllers\Backend\Customer;

use Illuminate\Http\Request;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Requests\Backend\Customer\CustomerGroupFormRequest;
use Kommercio\Models\Customer\CustomerGroup;

class CustomerGroupController extends Controller{
    public function index()
    {
        $qb = CustomerGroup::orderBy('sort_order', 'ASC');

        $customerGroups = $qb->get();

        return view('backend.customer.customer_group.index', [
            'customerGroups' => $customerGroups,
        ]);
    }

    public function create()
    {
        $customerGroup = new CustomerGroup();

        return view('backend.customer.customer_group.create', [
            'customerGroup' => $customerGroup
        ]);
    }

    public function store(CustomerGroupFormRequest $request)
    {
        $customerGroup = new CustomerGroup();
        $customerGroup->fill($request->all());
        $customerGroup->save();

        return redirect($request->get('backUrl', route('backend.customer.group.index')))->with('success', [$customerGroup->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $customerGroup = CustomerGroup::findOrFail($id);

        return view('backend.customer.customer_group.edit', [
            'customerGroup' => $customerGroup
        ]);
    }

    public function update(CustomerGroupFormRequest $request, $id)
    {
        $customerGroup = CustomerGroup::findOrFail($id);

        $customerGroup->fill($request->all());
        $customerGroup->save();

        return redirect($request->get('backUrl', route('backend.customer.group.index')))->with('success', [$customerGroup->name.' has successfully been updated.']);
    }

    public function delete($id)
    {
        $customerGroup = CustomerGroup::findOrFail($id);

        $name = $customerGroup->name;

        $customerGroup->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }

    public function reorder(Request $request)
    {
        foreach($request->input('objects') as $idx=>$object){
            $customerGroup = CustomerGroup::findOrFail($object);
            $customerGroup->update([
                'sort_order' => $idx
            ]);
        }

        if($request->ajax()){
            return response()->json([
                'result' => 'success',
            ]);
        }else{
            return redirect()->route('backend.customer.group.index');
        }
    }
}