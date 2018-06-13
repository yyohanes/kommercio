<?php

namespace Kommercio\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Kommercio\Events\StoreEvent;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Store;
use Kommercio\Models\User;

class ChamberController extends Controller{
    public function dashboard()
    {
        return view('backend.dashboard');
    }

    public function changeStore(Request $request, $id)
    {
        $user = Auth::user();
        $managedStores = $user->getManagedStores()->pluck('id')->all();
        $store = Store::findOrFail($id);

        if(!in_array($id, $managedStores)){
            abort(403);
        }

        Session::put('active_store', $store->id);

        Event::fire(new StoreEvent('store_change', $store));

        return redirect($request->input('backUrl', route('backend.dashboard')));
    }

    public function healthCheck()
    {
        return response('Healthy');
    }
}
