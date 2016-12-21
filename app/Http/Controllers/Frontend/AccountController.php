<?php

namespace Kommercio\Http\Controllers\Frontend;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Kommercio\Facades\LanguageHelper;
use Kommercio\Facades\NewsletterSubscriptionHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Customer;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Profile\Profile;

class AccountController extends Controller
{
    public $user;
    public $customer;

    public function __construct()
    {
        $this->user = Auth::user();
        $this->customer = $this->user?$this->user->customer:null;
    }

    public function account()
    {
        $viewName = ProjectHelper::getViewTemplate('frontend.member.dashboard');

        $seoData = [
            'meta_title' => trans(LanguageHelper::getTranslationKey('frontend.seo.member.dashboard.meta_title'))
        ];

        $qb = Order::checkout()->where('customer_id', $this->customer->id);
        $recentOrders = $qb->take(5)->get();

        $defaultProfiles = $this->customer->getDefaultProfiles();

        return view($viewName, [
            'user' => $this->user,
            'customer' => $this->customer,
            'seoData' => $seoData,
            'recentOrders' => $recentOrders,
            'defaultProfiles' => $defaultProfiles
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

            if ($request->ajax()) {
                return new JsonResponse([
                    'success' => [trans(LanguageHelper::getTranslationKey('frontend.member.profile_update.success_message'))],
                    '_token' => csrf_token()
                ]);
            }

            return redirect()->back()->with('success', [trans(LanguageHelper::getTranslationKey('frontend.member.profile_update.success_message'))]);
        }

        $seoData = [
            'meta_title' => trans(LanguageHelper::getTranslationKey('frontend.seo.member.profile.meta_title'))
        ];

        $viewName = ProjectHelper::getViewTemplate('frontend.member.account.profileUpdate');

        return view($viewName, [
            'user' => $this->user,
            'customer' => $this->customer,
            'seoData' => $seoData
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

            if ($request->ajax()) {
                return new JsonResponse([
                    'success' => [trans(LanguageHelper::getTranslationKey('frontend.member.account_update.success_message'))],
                    '_token' => csrf_token()
                ]);
            }

            return redirect()->back()->with('success', [trans(LanguageHelper::getTranslationKey('frontend.member.account_update.success_message'))]);
        }

        $seoData = [
            'meta_title' => trans(LanguageHelper::getTranslationKey('frontend.seo.member.account.meta_title'))
        ];

        $viewName = ProjectHelper::getViewTemplate('frontend.member.account.accountUpdate');

        return view($viewName, [
            'user' => $this->user,
            'customer' => $this->customer,
            'seoData' => $seoData
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

        $seoData = [
            'meta_title' => trans(LanguageHelper::getTranslationKey('frontend.seo.member.order.history.meta_title'))
        ];

        $view_name = ProjectHelper::getViewTemplate('frontend.member.orders.index');

        return view($view_name, [
            'user' => $this->user,
            'customer' => $this->customer,
            'orders' => $orders,
            'options' => $options,
            'seoData' => $seoData
        ]);
    }

    public function viewOrder($reference)
    {
        $order = Order::where('reference', $reference)->firstOrFail();

        //If doesn't belong to him
        if($order->customer_id != $this->customer->id){
            return redirect()->route('frontend.member.orders');
        }

        $seoData = [
            'meta_title' => trans(LanguageHelper::getTranslationKey('frontend.seo.member.order.view.meta_title'), ['order_reference' => $order->reference])
        ];

        $view_name = ProjectHelper::getViewTemplate('frontend.member.orders.view');

        return view($view_name, [
            'user' => $this->user,
            'customer' => $this->customer,
            'order' => $order,
            'seoData' => $seoData
        ]);
    }

    public function addressIndex()
    {
        $user = Auth::user();
        $profiles = $user->customer->savedProfiles;

        $seoData = [
            'meta_title' => trans(LanguageHelper::getTranslationKey('frontend.seo.member.address_book.index.meta_title'))
        ];

        $view_name = ProjectHelper::getViewTemplate('frontend.member.address.index');

        return view($view_name, [
            'user' => $this->user,
            'customer' => $this->customer,
            'profiles' => $profiles,
            'seoData' => $seoData
        ]);
    }

    public function addressCreate(Request $request)
    {
        $user = $request->user();
        $customer = $user->customer;
        $profile = new Profile();

        $billingDefault = old('billing', empty($customer->defaultBillingProfile));
        $shippingDefault = old('shipping', empty($customer->defaultShippingProfile));

        $seoData = [
            'meta_title' => trans(LanguageHelper::getTranslationKey('frontend.seo.member.address_book.create.meta_title'))
        ];

        $view_name = ProjectHelper::getViewTemplate('frontend.member.address.create');

        return view($view_name, [
            'user' => $this->user,
            'customer' => $this->customer,
            'profile' => $profile,
            'billingDefault' => $billingDefault,
            'shippingDefault' => $shippingDefault,
            'seoData' => $seoData
        ]);
    }

    public function addressEdit(Request $request, $id)
    {
        $user = $request->user();
        $customer = $user->customer;

        $profile = $customer->savedProfiles()->where('profile_id', $id)->firstOrFail();
        $profile->getDetails();

        Session::flashInput([
            'name' => $profile->pivot->name,
            'profile' => $profile->getDetails(),
            'shipping' => $profile->pivot->shipping,
            'billing' => $profile->pivot->billing,
        ]);

        $billingDefault = old('billing', $profile->pivot->billing)?true:false;
        $shippingDefault = old('shipping', $profile->pivot->shipping)?true:false;

        $seoData = [
            'meta_title' => trans(LanguageHelper::getTranslationKey('frontend.seo.member.address_book.edit.meta_title'))
        ];

        $view_name = ProjectHelper::getViewTemplate('frontend.member.address.edit');

        return view($view_name, [
            'user' => $this->user,
            'customer' => $this->customer,
            'profile' => $profile,
            'billingDefault' => $billingDefault,
            'shippingDefault' => $shippingDefault,
            'seoData' => $seoData
        ]);
    }

    public function addressSave(Request $request, $id = null)
    {
        $user = $request->user();
        $customer = $user->customer;

        if($id){
            $profile = $customer->savedProfiles()->where('profile_id', $id)->firstOrFail();
        }else{
            $profile = new Profile();
        }

        $rules = [
            'name' => 'in:'.implode(',', array_keys(Customer::getProfileNameOptions())),
            'profile.salute' => 'in:'.implode(',', array_keys(Customer::getSaluteOptions())),
            'profile.full_name' => 'required',
            'profile.phone_number' => 'required',
            'profile.home_phone' => '',
            'profile.address_1' => 'required',
            'profile.country_id' => 'required',
            'profile.state_id' => 'descendant_address:state',
            'profile.city_id' => 'descendant_address:city',
            'profile.district_id' => 'descendant_address:district',
            'profile.area_id' => 'descendant_address:area',
            'billing' => 'boolean',
            'shipping' => 'boolean',
        ];

        $this->validate($request, $rules);

        $profile->profileable()->associate($customer);
        $profile->save();

        $profile->saveDetails($request->input('profile'));

        $syncData = [];

        //Un-default other saved profiles
        foreach($customer->savedProfiles as $savedProfile){
            $syncData[$savedProfile->id] = [
                'name' => $savedProfile->pivot->name
            ];

            $syncData[$savedProfile->id]['shipping'] = $request->has('shipping')?false:$savedProfile->pivot->shipping;
            $syncData[$savedProfile->id]['billing'] = $request->has('billing')?false:$savedProfile->pivot->billing;
        }

        $syncData[$profile->id] = [
            'name' => $request->input('name'),
            'shipping' => $request->has('shipping'),
            'billing' => $request->has('billing'),
        ];

        $customer->savedProfiles()->detach();
        $customer->savedProfiles()->sync($syncData);

        if($id){
            $message = trans(LanguageHelper::getTranslationKey('frontend.member.address.edit_success_message'));
        }else{
            $message = trans(LanguageHelper::getTranslationKey('frontend.member.address.create_success_message'));
        }

        if($request->ajax()){
            return response()->json([
                'message' => $message,
                '_token' => csrf_token(),
            ]);
        }else{
            return redirect()->route('frontend.member.address.index')->with('success', [$message]);
        }
    }

    public function addressSetDefault(Request $request, $id, $type)
    {
        $user = $request->user();
        $customer = $user->customer;

        $profile = $customer->savedProfiles()->where('profile_id', $id)->firstOrFail();
        $profile->getDetails();

        $rules = [
            'type' => 'in:shipping,billing',
        ];

        $validator = Validator::make([
            'type' => $type,
        ], $rules);

        if ($validator->fails()) {
            $this->throwValidationException($request, $validator);
        }

        $syncData = [];

        //Un-default other saved profiles
        foreach($customer->savedProfiles as $savedProfile){
            $syncData[$savedProfile->id] = [
                'name' => $savedProfile->pivot->name
            ];

            if($type == 'shipping'){
                $syncData[$savedProfile->id]['shipping'] = $savedProfile->id == $id;
            }elseif($type == 'billing'){
                $syncData[$savedProfile->id]['billing'] = $savedProfile->id == $id;
            }
        }

        $customer->savedProfiles()->detach();
        $customer->savedProfiles()->sync($syncData);

        $message = trans(LanguageHelper::getTranslationKey('frontend.member.address.set_default_success_message'), [
            'address' => ($profile->pivot->name?Customer::getProfileNameOptions($profile->pivot->name).' - ':'').str_limit($profile->address_1, 50),
            'type' => $type
        ]);

        if($request->ajax()){
            return response()->json([
                'message' => $message,
                '_token' => csrf_token(),
            ]);
        }else{
            return redirect()->route('frontend.member.address.index')->with('success', [$message]);
        }
    }

    public function addressDelete(Request $request, $id)
    {
        $profile = Profile::findOrFail($id);

        $profile->delete();

        $message = trans(LanguageHelper::getTranslationKey('frontend.member.address.delete_success_message'));

        if($request->ajax()){
            return response()->json([
                'message' => $message,
                '_token' => csrf_token(),
            ]);
        }else{
            return redirect()->route('frontend.member.address.index')->with('success', [$message]);
        }
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

        NewsletterSubscriptionHelper::subscribe($request->input('group'), $request->input('email'), $request->input('name', null), $request->input('last_name', null), $request->input('fields', []));

        if($request->ajax()){
            return new JsonResponse([
                'message' => trans(LanguageHelper::getTranslationKey('frontend.member.newsletter.subscription_success_message')),
                '_token' => csrf_token()
            ]);
        }

        return redirect()->back()->with('success', [trans(LanguageHelper::getTranslationKey('frontend.member.newsletter.subscription_success_message'))]);
    }
}
