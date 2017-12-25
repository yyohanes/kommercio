<?php

namespace Kommercio\Http\Controllers\Frontend\Auth;

use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kommercio\Facades\LanguageHelper;
use Kommercio\Facades\NewsletterSubscriptionHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\User;
use Kommercio\Models\Customer;
use Illuminate\Support\Facades\Validator;
use Kommercio\Http\Controllers\Controller;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesUsers{
        showLoginForm as parentShowLoginForm;
        sendFailedLoginResponse as parentSendFailedLoginResponse;
        AuthenticatesUsers::guard insteadof RegistersUsers;
        AuthenticatesUsers::redirectPath insteadof RegistersUsers;
    }

    use RegistersUsers{
        showRegistrationForm as parentShowRegistrationForm;
        register as parentRegister;
    }

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';
    protected $loginView;
    protected $redirectAfterLogout;

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->middleware('guest', ['except' => 'getLogout']);

        $this->redirectAfterLogout = $request->get('to', route('frontend.login_form'));
        $this->redirectTo = route('frontend.member.account');

        $this->loginView = ProjectHelper::getViewTemplate('frontend.auth.login');
        $this->registerView = ProjectHelper::getViewTemplate('frontend.auth.register');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
        ]);
    }

    /*
     * Override parent trait to add SEO Data
     */
    public function showLoginForm()
    {
        $view = property_exists($this, 'loginView')
            ? $this->loginView : 'auth.authenticate';

        if (!view()->exists($view)) {
            $view = 'auth.login';
        }

        $seoData = [
            'meta_title' => trans(LanguageHelper::getTranslationKey('frontend.seo.member.login.meta_title'))
        ];

        return view($view, [
            'seoData' => $seoData
        ]);
    }

    /*
     * Override parent trait to add SEO Data
     */
    public function showRegistrationForm()
    {
        $view = 'auth.register';

        if (property_exists($this, 'registerView')) {
            $view = $this->registerView;
        }

        $seoData = [
            'meta_title' => trans(LanguageHelper::getTranslationKey('frontend.seo.member.register.meta_title'))
        ];

        return view($view, [
            'seoData' => $seoData
        ]);
    }

    /*
     * Override parent trait to add AJAX response
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);

        if ($request->ajax()) {
            return new JsonResponse([
                'redirect' => $this->redirectPath(),
                '_token' => csrf_token()
            ]);
        }

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }

    /*
     * If ajax, return redirect URL
     */
    protected function authenticated(Request $request, $user)
    {
        if ($request->ajax()) {
            return new JsonResponse([
                'redirect' => $request->session()->pull('url.intended', $this->redirectPath()),
                '_token' => csrf_token()
            ]);
        }

        return redirect()->intended($this->redirectPath());
    }

    /*
     * If ajax, return redirect URL
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        if ($request->ajax()) {
            return new JsonResponse([$this->username() => [trans('auth.failed')]], 403);
        }

        return redirect()->back()
            ->withInput($request->only($this->username(), 'remember'))
            ->withErrors([
                $this->username() => trans('auth.failed'),
            ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        $accountData = [
            'email' => $data['email'],
            'status' => User::STATUS_ACTIVE,
            'password' => $data['password']
        ];

        $profileData = [
            'full_name' => $data['name'],
            'email' => $data['email']
        ];

        $customer = Customer::saveCustomer(null, $profileData, $accountData, true, true);

        if(isset($data['signup_newsletter']) && $data['signup_newsletter'] == 1){
            NewsletterSubscriptionHelper::subscribe('default', $accountData['email'], $profileData['full_name']);
        }

        return $customer->user;
    }

    public function getLogout(Request $request)
    {
        $this->logout($request);

        return redirect($this->redirectAfterLogout);
    }

    public function getToken(Request $request)
    {
        return response(csrf_token());
        if ($request->ajax()) {
            return response(csrf_token());
        }

        abort(500);
    }
}
