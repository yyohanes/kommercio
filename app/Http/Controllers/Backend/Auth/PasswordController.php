<?php

namespace Kommercio\Http\Controllers\Backend\Auth;

use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Kommercio\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;

class PasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    protected $linkRequestView;

    use SendsPasswordResetEmails, ResetsPasswords {
        ResetsPasswords::broker as resetPasswordsBroker;
        SendsPasswordResetEmails::broker insteadof ResetsPasswords;
    }

    /**
     * Create a new password controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('backend.guest');

        $this->redirectPath = route('backend.login_form');
        $this->linkRequestView = 'backend.auth.passwords.email';
        $this->resetView = 'backend.auth.passwords.reset';
    }
}
