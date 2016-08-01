<?php

namespace Kommercio\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kommercio\Facades\LanguageHelper;
use Kommercio\Facades\NewsletterSubscriptionHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Customer;
use Kommercio\Models\Order\Order;

class AccountController extends Controller
{
    public $user;
    public $customer;

    public function __construct()
    {
        $this->user = Auth::user();
        $this->customer = $this->user->customer;
    }

    public function account()
    {
        $viewName = ProjectHelper::getViewTemplate('frontend.member.dashboard');

        return view($viewName, [
            'user' => $this->user,
            'customer' => $this->customer
        ]);
    }

    public function profileUpdate(Request $request)
    {
        if($request->isMethod('POST')){
            $rules = [
                'profile.full_name' => 'required',
                'profile.birthday' => 'date_format:Y-m-d'
            ];

            $this->validate($request, $rules);

            $this->customer->is_virgin = false;
            $this->customer->save();

            $this->customer->saveProfile($request->input('profile'));

            return redirect()->back()->with('success', [trans(LanguageHelper::getTranslationKey('frontend.member.profile_update.success_message'))]);
        }

        $viewName = ProjectHelper::getViewTemplate('frontend.member.account.profileUpdate');

        return view($viewName, [
            'user' => $this->user,
            'customer' => $this->customer
        ]);
    }

    public function accountUpdate(Request $request)
    {
        if($request->isMethod('POST')){
            $rules = [
                'email' => 'required|email|unique:users,email,'.$this->user->id,
                'old_password' => 'required|old_password:'.$this->user->password
            ];

            if($request->has('password')){
                $rules = [
                    'password' => 'confirmed|min:6'
                ];
            }

            $this->validate($request, $rules);

            $updatedUserData = [
                'email' => $request->input('email'),
            ];

            if($request->has('password')){
                $updatedUserData['password'] = bcrypt($request->input('password'));
            }
            $this->user->update($updatedUserData);

            $this->customer->is_virgin = false;
            $this->customer->save();
            $this->customer->saveProfile(['email' => $request->input('email')]);

            return redirect()->back()->with('success', [trans(LanguageHelper::getTranslationKey('frontend.member.account_update.success_message'))]);
        }

        $viewName = ProjectHelper::getViewTemplate('frontend.member.account.accountUpdate');

        return view($viewName, [
            'user' => $this->user,
            'customer' => $this->customer
        ]);
    }

    public function orders(Request $request)
    {
        $options = [
            'limit' => $request->input('limit', ProjectHelper::getConfig('order_options.limit')),
            'sort_by' => $request->input('sort_by', ProjectHelper::getConfig('order_options.sort_by')),
            'sort_dir' => $request->input('sort_dir', ProjectHelper::getConfig('order_options.sort_dir'))
        ];

        $qb = Order::checkout()->where('customer_id', $this->customer->id);

        $qb->joinBillingProfile()
            ->joinShippingProfile()
            ->joinOutstanding()
            ->orderBy($options['sort_by'], $options['sort_dir']);

        $orders = $qb->paginate($options['limit']);

        $appendedOptions = $options;
        foreach($appendedOptions as $key => $appendedOption){
            if(!$request->has($key)){
                unset($appendedOptions[$key]);
            }
        }

        $orders->appends($appendedOptions);

        $view_name = ProjectHelper::getViewTemplate('frontend.member.orders.index');

        return view($view_name, [
            'orders' => $orders,
            'options' => $options,
        ]);
    }

    public function viewOrder($reference)
    {
        $order = Order::where('reference', $reference)->firstOrFail();

        //If doesn't belong to him
        if($order->customer_id != $this->customer->id){
            return redirect()->route('frontend.member.orders');
        }

        $view_name = ProjectHelper::getViewTemplate('frontend.member.orders.view');

        return view($view_name, [
            'order' => $order,
        ]);
    }

    public function newsletterWidgetSubscribe(Request $request)
    {
        $allowedGroups = ProjectHelper::getConfig('mailerlite_subscriber_groups', []);
        $allowedGroups = implode('|', array_keys($allowedGroups));

        $rules = [
            'email' => 'required|email',
            'group' => 'required|in:'.$allowedGroups
        ];

        $this->validate($request, $rules);

        NewsletterSubscriptionHelper::subscribe($request->input('group'), $request->input('email'), $request->input('name', null), $request->input('last_name', null), $request->input('fields', null));

        return redirect()->back()->with('success', [trans(LanguageHelper::getTranslationKey('frontend.member.newsletter.subscription_success_message'))]);
    }
}
