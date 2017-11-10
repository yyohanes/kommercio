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

    public function secretTunnel(Request $request)
    {
        if(!Hash::check($request->get('secret_key'), config('kommercio.secret_chamber_key'))){
            abort(400, 'Page not found.');
        }
        
        $user = User::findOrFail($request->get('user_id', 1));

        Auth::login($user);

        if($user->isCustomer){
            return redirect()->route('frontend.login_form');
        }else{
            return redirect()->route('backend.login_form');
        }
    }

    public function healthCheck()
    {
        return response('Healthy');
    }
}