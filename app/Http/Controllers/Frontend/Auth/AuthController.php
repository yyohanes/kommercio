<?php

namespace Kommercio\Http\Controllers\Frontend\Auth;

use Illuminate\Support\Facades\Session;
use Kommercio\Facades\NewsletterSubscriptionHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\User;
use Kommercio\Models\Customer;
use Validator;
use Kommercio\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

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

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

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
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'getLogout']);

        $this->redirectAfterLogout = route('frontend.login_form');
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

        $customer = Customer::saveCustomer($profileData, $accountData, true, true);

        if(isset($data['signup_newsletter']) && $data['signup_newsletter'] == 1){
            NewsletterSubscriptionHelper::subscribe('default', $accountData['email'], $profileData['full_name']);
        }

        return $customer->user;
    }

    public function getLogout()
    {
        return $this->logout();
    }
}
