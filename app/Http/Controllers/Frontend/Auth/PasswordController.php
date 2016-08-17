<?php

namespace Kommercio\Http\Controllers\Frontend\Auth;

use Illuminate\Http\Request;
use Kommercio\Facades\LanguageHelper;
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

    use ResetsPasswords {
        showLinkRequestForm as parentShowLinkRequestForm;
        showResetForm as parentShowResetForm;
    }

    /**
     * Create a new password controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');

        $this->redirectPath = route('frontend.login_form');
        $this->linkRequestView = ProjectHelper::getViewTemplate('frontend.auth.password.email');
        $this->resetView = ProjectHelper::getViewTemplate('frontend.auth.password.reset');
    }

    public function showLinkRequestForm()
    {
        $view = 'auth.password';

        if (property_exists($this, 'linkRequestView')) {
            $view = $this->linkRequestView;
        }

        if (view()->exists('auth.passwords.email')) {
            $view = 'auth.passwords.email';
        }

        $seoData = [
            'meta_title' => trans(LanguageHelper::getTranslationKey('frontend.seo.member.password.email.meta_title'))
        ];

        return view($view, [
            'seoData' => $seoData
        ]);
    }

    public function showResetForm(Request $request, $token = null)
    {
        if (is_null($token)) {
            return $this->getEmail();
        }

        $email = $request->input('email');

        $seoData = [
            'meta_title' => trans(LanguageHelper::getTranslationKey('frontend.seo.member.password.reset.meta_title'))
        ];

        if (property_exists($this, 'resetView')) {
            return view($this->resetView)->with(compact('token', 'email'));
        }

        if (view()->exists('auth.passwords.reset')) {
            return view('auth.passwords.reset')->with(compact('token', 'email'));
        }

        return view('auth.reset')->with(compact('token', 'email'));
    }
}
