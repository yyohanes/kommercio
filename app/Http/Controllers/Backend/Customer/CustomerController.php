<?php

namespace Kommercio\Http\Controllers\Backend\Customer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Customer;
use Kommercio\Http\Requests\Backend\Customer\CustomerFormRequest;
use Collective\Html\FormFacade;
use Illuminate\Support\Facades\Request as RequestFacade;
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
            $customerAction .= '<div class="btn-group btn-group-sm">';
            if(Gate::allows('access', ['edit_customer'])):
                $customerAction .= '<a class="btn btn-default" href="'.route('backend.customer.edit', ['id' => $customer->id, 'backUrl' => RequestFacade::fullUrl()]).'"><i class="fa fa-pencil"></i> Edit</a>';
            endif;
            if(Gate::allows('access', ['delete_customer'])):
                $customerAction .= '<button class="btn btn-default" data-toggle="confirmation" data-original-title="Are you sure?" title=""><i class="fa fa-trash-o"></i> Delete</button></div>';
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

    protected function deleteable(Customer $customer)
    {
        return $customer->orders()->count() < 1;
    }
}