<?php

namespace Kommercio\Http\Controllers\Backend\Warehouse;

use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Requests\Backend\Warehouse\WarehouseFormRequest;
use Kommercio\Models\Warehouse;

class WarehouseController extends Controller{
    public function index()
    {
        $qb = Warehouse::orderBy('created_at', 'DESC');

        $warehouses = $qb->get();

        return view('backend.warehouse.index', [
            'warehouses' => $warehouses,
        ]);
    }

    public function create()
    {
        $warehouse = new Warehouse();

        return view('backend.warehouse.create', [
            'warehouse' => $warehouse
        ]);
    }

    public function store(WarehouseFormRequest $request)
    {
        $warehouse = new Warehouse();
        $warehouse->fill($request->all());
        $warehouse->save();

        return redirect($request->get('backUrl', route('backend.warehouse.index')))->with('success', [$warehouse->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $warehouse = Warehouse::findOrFail($id);

        return view('backend.warehouse.edit', [
            'warehouse' => $warehouse
        ]);
    }

    public function update(WarehouseFormRequest $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $warehouse->fill($request->all());
        $warehouse->save();

        return redirect($request->get('backUrl', route('backend.warehouse.index')))->with('success', [$warehouse->name.' has successfully been updated.']);
    }

    public function delete($id)
    {
        $warehouse = Warehouse::findOrFail($id);

        if(!$this->isDeleteable($warehouse)){
            return redirect()->back()->withErrors(['This warehouse can\'t be deleted because it contains products.']);
        }

        $name = $warehouse->name;

        $warehouse->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }

    public function isDeleteable(Warehouse $warehouse)
    {
        return $warehouse->productCount < 1;
    }
}