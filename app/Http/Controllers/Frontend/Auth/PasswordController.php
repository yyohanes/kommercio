<?php

namespace Kommercio\Http\Controllers\Frontend\Auth;

use Kommercio\Facades\ProjectHelper;
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

    use ResetsPasswords;

    /**
     * Create a new password controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');

        $this->linkRequestView = ProjectHelper::getViewTemplate('frontend.auth.password.email');
    }
}
