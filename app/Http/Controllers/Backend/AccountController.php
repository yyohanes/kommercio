<?php

namespace Kommercio\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kommercio\Http\Controllers\Controller;

class AccountController extends Controller{
    public function credentials(Request $request)
    {
        $user = Auth::user();

        if($request->isMethod('POST')){
            $this->validate($request, $this->getCredentialsRules($request, $user));

            $user->update([
                'email' => $request->input('email'),
                'password' => bcrypt($request->input('password'))
            ]);

            return redirect()->back()->with('success', ['Your credentials is successfully updated.']);
        }

        return view('backend.account.credentials', [
            'user' => $user
        ]);
    }

    public function profile(Request $request)
    {
        $user = Auth::user();
        $user->load('profile');

        if($request->isMethod('POST')){
            $this->validate($request, $this->getProfileRules());

            $user->saveProfile($request->input('profile'));

            return redirect()->back()->with('success', ['Your profile is successfully updated.']);
        }

        return view('backend.account.profile', [
            'user' => $user
        ]);
    }

    protected function getCredentialsRules($request, $user=null)
    {
        $changed = $request->input('email') != $user->email;

        $rules = [
            'email' => 'required|email|unique:users,email'.($user?','.$user->id:''),
            'password' => 'confirmed|min:6'.($changed?'|required':'')
        ];

        return $rules;
    }

    protected function getProfileRules()
    {
        $rules = [
            'profile.full_name' => 'required',
            //'profile.phone_number' => 'required'
        ];

        return $rules;
    }
}