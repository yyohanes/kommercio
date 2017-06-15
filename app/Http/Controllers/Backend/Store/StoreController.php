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
        $openingTimes = collect([new Store\OpeningTime(['open' => TRUE])]);

        return view('backend.store.create', [
            'store' => $store,
            'openingTimes' => $openingTimes
        ]);
    }

    public function store(StoreFormRequest $request)
    {
        $store = new Store();
        $store->fill($request->input('location') + $request->all());
        $store->setData('contacts', $request->input('contacts'));
        $store->save();

        $this->processOpeningTimes($store, $request->input('openingTimes'));

        $store->warehouses()->sync($request->input('warehouses', []));

        return redirect($request->get('backUrl', route('backend.store.index')))->with('success', [$store->name.' has successfully been created.']);
    }

    public function edit($id)
    {
        $store = Store::with('warehouses')->findOrFail($id);
        $openingTimes = $store->openingTimes->count()>0?$store->openingTimes:collect([new Store\OpeningTime(['open' => TRUE])]);

        return view('backend.store.edit', [
            'store' => $store,
            'openingTimes' => $openingTimes
        ]);
    }

    public function update(StoreFormRequest $request, $id)
    {
        $store = Store::findOrFail($id);

        $store->fill($request->input('location') + $request->all());
        $store->setData('contacts', $request->input('contacts'));
        $store->save();

        $this->processOpeningTimes($store, $request->input('openingTimes'));

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

    /**
     * Process submitted openingTimes
     *
     * @param Store $store processed store
     * @param array $openingTimeInputs openingTimes input from request
     */
    protected function processOpeningTimes(Store $store, $openingTimeInputs)
    {
        // For later comparison to find deleted ids
        $oldOpeningTimeIds = $store->openingTimes->pluck('id');
        $newOpeningTimeIds = collect([]);

        $count = 0;
        foreach($openingTimeInputs as $openingTimeInput){
            $openingTime = null;

            if (!empty($openingTimeInput['id'])) {
                $openingTime = $store->openingTimes()->where('id', $openingTimeInput['id'])->first();
            }

            if(!$openingTime){
                $openingTime = new Store\OpeningTime();
                $openingTime->store()->associate($store);
            }

            $openingTime->fill($openingTimeInput);
            $openingTime->sort_order = $count;
            $openingTime->save();

            $newOpeningTimeIds->push($openingTime->id);

            $count += 1;
        }

        $toBeDeleted = $oldOpeningTimeIds->diff($newOpeningTimeIds);
        foreach ($toBeDeleted as $toBeDeletedId) {
            $toBeDeletedOpeningTime = Store\OpeningTime::find($toBeDeletedId);

            if ($toBeDeletedOpeningTime) {
                $toBeDeletedOpeningTime->delete();
            }
        }
    }

    protected function deleteable(Store $store)
    {
        return $store->orderCount < 1;
    }
}