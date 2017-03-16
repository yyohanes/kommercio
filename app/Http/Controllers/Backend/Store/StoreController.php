<?php

namespace Kommercio\Http\Controllers\Backend\Store;

use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Requests\Backend\Store\StoreFormRequest;
use Kommercio\Models\Store;

class StoreController extends Controller{
    public function index()
    {
        $qb = Store::orderBy('created_at', 'DESC');

        $stores = $qb->get();

        return view('backend.store.index', [
            'stores' => $stores,
        ]);
    }

    public function create()
    {
        $store = new Store();

        return view('backend.store.create', [
            'store' => $store
        ]);
    }

    public function store(StoreFormRequest $request)
    {
        $store = new Store();
        $store->fill($request->input('location') + $request->all());
        $store->setData('contacts', $request->input('contacts'));
        $store->save();

        $store->warehouses()->sync($request->input('warehouses', []));

        return redirect($request->get('backUrl', route('backend.store.index')))->with('success', [$store->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $store = Store::with('warehouses')->findOrFail($id);

        return view('backend.store.edit', [
            'store' => $store
        ]);
    }

    public function update(StoreFormRequest $request, $id)
    {
        $store = Store::findOrFail($id);

        $store->fill($request->input('location') + $request->all());
        $store->setData('contacts', $request->input('contacts'));
        $store->save();

        $store->warehouses()->sync($request->input('warehouses', []));

        return redirect($request->get('backUrl', route('backend.store.index')))->with('success', [$store->name.' has successfully been updated.']);
    }

    public function delete($id)
    {
        $store = Store::findOrFail($id);

        $name = $store->name;

        if(!$this->deleteable($store)){
            return redirect()->back()->withErrors('There are Orders in this store, thus can no longer be deleted.');
        }

        $store->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }

    protected function deleteable(Store $store)
    {
        return $store->orderCount < 1;
    }
}