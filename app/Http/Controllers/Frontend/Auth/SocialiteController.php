<?php

namespace Kommercio\Http\Controllers\Frontend\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\SocialAccount;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function facebookRedirect(Request $request)
    {
        $driver = Socialite::driver('facebook');

        if($request->input('popup')){
            $driver->asPopup();
        }

        if($request->has('cancelUrl')){
            Session::put('social_cancel_url', $request->input('cancelUrl'));
        }

        if($request->has('continueUrl')){
            Session::put('social_continue_url', $request->input('continueUrl'));
        }

        return $driver->redirect();
    }

    public function facebookCallback(Request $request)
    {
        $driver = Socialite::driver('facebook');

        if($request->has('error')){
            $redirectResponse = redirect(Session::get('social_cancel_url', route('frontend.login')));
        }else{
            $redirectResponse = redirect(Session::get('social_continue_url', route('frontend.login')));

            $user = SocialAccount::createOrGetUser($driver->user(), 'facebook');

            Auth::guard()->login($user);
        }

        $this->clearSocialUrlFromSession();

        return $redirectResponse;
    }

    protected function clearSocialUrlFromSession()
    {
        Session::forget('social_cancel_url');
        Session::forget('social_continue_url');
    }
}
