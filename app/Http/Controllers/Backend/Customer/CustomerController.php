<?php

namespace Kommercio\Http\Controllers\Backend\Customer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Kommercio\Facades\PriceFormatter;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Customer;
use Kommercio\Http\Requests\Backend\Customer\CustomerFormRequest;
use Collective\Html\FormFacade;
use Illuminate\Support\Facades\Request as RequestFacade;
use Kommercio\Models\Profile\Profile;
use Kommercio\Models\User;

class CustomerController extends Controller{
    public function index(Request $request)
    {
        $qb = Customer::with('profile', 'user')
            ->joinFullName()
            ->joinFields(['email', 'salute']);

        if($request->ajax() || $request->wantsJson()){
            $totalRecords = $qb->count();

            foreach($request->input('filter', []) as $searchKey=>$search){
                if(trim($search) != ''){
                    if($searchKey == 'account') {
                        if($search == 1){
                            $qb->whereNotNull('user_id');
                        }else{
                            $qb->whereNull('user_id');
                        }
                    }elseif($searchKey == 'status') {
                        $qb->whereUserStatus($search);
                    }elseif($searchKey == 'full_name') {
                        $qb->whereRaw('CONCAT(VFNAME.value, " ", VLNAME.value) LIKE ?', ['%'.$search.'%']);
                    }elseif($searchKey == 'salute'){
                        $qb->whereField($searchKey, $search);
                    }else{
                        $qb->whereField($searchKey, '%'.$search.'%', 'LIKE');
                    }
                }
            }

            $filteredRecords = $qb->count();

            $qb->joinOrderTotal();

            $columns = $request->input('columns');
            foreach($request->input('order', []) as $order){
                $orderColumn = $columns[$order['column']];

                $qb->orderBy($orderColumn['name'], $order['dir']);
            }

            if($request->has('length')){
                $qb->take($request->input('length'));
            }

            if($request->has('start') && $request->input('start') > 0){
                $qb->skip($request->input('start'));
            }

            $customers = $qb->get();

            $meat = $this->prepareDatatables($customers, $request->input('start'));

            $data = [
                'draw' => $request->input('draw'),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $meat
            ];

            return response()->json($data);
        }

        return view('backend.customer.index');
    }

    protected function prepareDatatables($customers, $orderingStart=0)
    {
        $meat= [];

        foreach($customers as $idx=>$customer){
            $customer->loadProfileFields();

            $customerAction = FormFacade::open(['route' => ['backend.customer.delete', 'id' => $customer->id]]);
            $customerAction .= '<div class="btn-group btn-group-xs">';
            if(Gate::allows('access', ['view_customer'])):
                $customerAction .= '<a class="btn btn-default" href="'.route('backend.customer.view', ['id' => $customer->id, 'backUrl' => RequestFacade::fullUrl()]).'"><i class="fa fa-search"></i></a>';
            endif;
            if(Gate::allows('access', ['edit_customer'])):
                $customerAction .= '<a class="btn btn-default" href="'.route('backend.customer.edit', ['id' => $customer->id, 'backUrl' => RequestFacade::fullUrl()]).'"><i class="fa fa-pencil"></i></a>';
            endif;
            if(Gate::allows('access', ['delete_customer'])):
                $customerAction .= '<button class="btn btn-default" data-toggle="confirmation" data-original-title="Are you sure?" title=""><i class="fa fa-trash-o"></i></button></div>';
            endif;
            $customerAction .= FormFacade::close();

            $meat[] = [
                $idx + 1 + $orderingStart,
                $customer->salute?Customer::getSaluteOptions($customer->salute):'',
                $customer->full_name,
                $customer->email,
                '<i class="fa fa-'.(isset($customer->user)?'check text-success':'remove text-danger').'"></i>',
                isset($customer->user)?'<i class="fa fa-'.($customer->user->status == User::STATUS_ACTIVE?'check text-success':'remove text-danger').'"></i>':'',
                $customer->created_at?$customer->created_at->format('d M Y H:i'):'',
                $customer->last_active?$customer->last_active->format('d M Y H:i'):'',
                PriceFormatter::formatNumber($customer->total),
                $customerAction
            ];
        }

        return $meat;
    }

    public function create()
    {
        $customer = new Customer();

        return view('backend.customer.create', [
            'customer' => $customer
        ]);
    }

    public function store(CustomerFormRequest $request)
    {
        $accountData = null;
        if($request->input('user.create_account')){
            $accountData = [
                'email' => $request->input('profile.email'),
                'status' => $request->input('user.status'),
            ];

            if($request->has('user.password')){
                $accountData['password'] = $request->input('user.password');
            }
        }

        $customer = Customer::saveCustomer($request->input('profile'), $accountData);

        return redirect($request->get('backUrl', route('backend.customer.index')))->with('success', ['New Customer is successfully created.']);
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);

        //Fill Profile Details
        $customer->load('user');
        $customer->loadProfileFields();

        return view('backend.customer.edit', [
            'customer' => $customer
        ]);
    }

    public function update(CustomerFormRequest $request, $id)
    {
        $accountData = null;
        if($request->input('user.create_account')){
            $accountData = [
                'email' => $request->input('profile.email'),
                'status' => $request->input('user.status')
            ];

            if($request->has('user.password')){
                $accountData['password'] = $request->input('user.password');
            }
        }
        $customer = Customer::saveCustomer($request->input('profile'), $accountData);

        return redirect($request->get('backUrl', route('backend.customer.index')))->with('success', ['Customer is successfully updated.']);
    }

    public function view($id)
    {
        $customer = Customer::joinOrderTotal()->where('id', $id)->first();

        //Fill Profile Details
        $customer->load(['user', 'orders']);
        $customer->loadProfileFields();

        return view('backend.customer.view', [
            'customer' => $customer
        ]);
    }

    public function delete($id)
    {
        $customer = Customer::findOrFail($id);

        if(!$this->deleteable($customer)){
            return redirect()->back()->withErrors(['This customer has Orders, therefore, it can\'t be deleted.']);
        }

        $name = 'Customer '.$customer->fullName;

        if(isset($customer->user)){
            $customer->user->delete();
        }

        $customerProfiles = $customer->profiles;
        foreach($customerProfiles as $customerProfile){
            $customerProfile->delete();
        }

        $customer->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }

    public function autocomplete(Request $request)
    {
        $return = [];
        $search = $request->get('query', '');

        if(!empty($search)){
            $qb = Customer::query();

            $filters[] = [
                'key' => 'first_name',
                'value' => '%'.$search.'%',
                'operator' => 'LIKE'
            ];

            $filters[] = [
                'key' => 'last_name',
                'value' => '%'.$search.'%',
                'operator' => 'LIKE'
            ];

            $filters[] = [
                'key' => 'email',
                'value' => '%'.$search.'%',
                'operator' => 'LIKE'
            ];

            $qb->whereFields($filters, TRUE);

            $results = $qb->get();

            foreach($results as $result){
                $return[] = [
                    'id' => $result->id,
                    'profile_id' => $result->profile?$result->profile->id:null,
                    'name' => $result->fullName.' ('.$result->getProfile()->email.')',
                    'email' => $result->getProfile()->email,
                    'tokens' => [
                        $result->getProfile()->first_name,
                        $result->getProfile()->last_name,
                        $result->getProfile()->email
                    ]
                ];
            }
        }

        return response()->json(['data' => $return, '_token' => csrf_token()]);
    }

    public function addressIndex($customer_id)
    {
        $customer = Customer::findOrFail($customer_id);

        $profiles = $customer->savedProfiles;

        $index = view('backend.customer.address.index', [
            'profiles' => $profiles,
            'customer' => $customer
        ])->render();

        return response()->json([
            'html' => $index,
            '_token' => csrf_token()
        ]);
    }

    public function addressForm($customer_id, $id = null)
    {
        $customer = Customer::findOrFail($customer_id);
        if($id){
            $profile = $customer->savedProfiles()->where('profile_id', $id)->firstOrFail();

            $profile->getDetails();
            Session::flashInput([
                'name' => $profile->pivot->name,
                'shipping' => $profile->pivot->shipping,
                'billing' => $profile->pivot->billing,
                'profile' => $profile->getDetails()
            ]);
        }else{
            $profile = new Profile();
        }

        $billingProfile = $customer->defaultBillingProfile;
        $shippingProfile = $customer->defaultShippingProfile;

        $form = view('backend.customer.address.form', [
            'profile' => $profile,
            'customer' => $customer,
            'billing' => false,
            'shipping' => false,
        ])->render();

        //Clear flashed input
        Session::pull('_old_input');

        return response()->json([
            'html' => $form,
            '_token' => csrf_token()
        ]);
    }

    public function addressSave(Request $request, $customer_id, $id = null)
    {
        $customer = Customer::findOrFail($customer_id);
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

        return response()->json([
            'result' => 'success',
            'message' => ($request->has('name')?Customer::getProfileNameOptions($request->input('name')).' ':'').'Address is successfully entered.'
        ]);
    }

    public function addressDelete(Request $request, $customer_id, $id)
    {
        $customer = Customer::findOrFail($customer_id);
        $profile = $customer->savedProfiles()->where('profile_id', $id)->firstOrFail();

        $message = ($profile->pivot->name?Customer::getProfileNameOptions($profile->pivot->name).' ':'').'Address is successfully deleted.';

        $profile->delete();

        return response()->json([
            'result' => 'success',
            'message' => $message
        ]);
    }

    protected function deleteable(Customer $customer)
    {
        return $customer->orders()->count() < 1;
    }
}