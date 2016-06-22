<?php

namespace Kommercio\Http\Controllers\Backend\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Requests\Backend\User\UserFormRequest;
use Kommercio\Models\Role\Role;
use Kommercio\Models\Store;
use Kommercio\Models\User;

class UserController extends Controller{
    public function index(Request $request)
    {
        $qb = User::with('profile', 'roles')->orderBy('created_at' , 'DESC')->notCustomer();

        $users = $qb->get();

        return view('backend.user.index', [
            'users' => $users
        ]);
    }

    public function create()
    {
        $user = new User();
        $roleOptions = Role::getRoleOptions();
        $storeOptions = Store::getStoreOptions();

        return view('backend.user.create', [
            'user' => $user,
            'roleOptions' => $roleOptions,
            'storeOptions' => $storeOptions
        ]);
    }

    public function store(UserFormRequest $request)
    {
        $accountData = [
            'email' => $request->input('email'),
            'status' => $request->input('status'),
        ];

        if($request->has('password')){
            $accountData['password'] = bcrypt($request->input('password'));
        }

        $user = User::create($accountData);
        $user->saveProfile($request->input('profile'));

        $user->roles()->sync([$request->input('role')]);
        $user->stores()->sync($request->input('stores', []));

        return redirect($request->get('backUrl', route('backend.user.index')))->with('success', ['New User is successfully created.']);
    }

    public function edit($id)
    {
        $currentUser = Auth::user();
        $user = User::findOrFail($id);

        if($user->isMasterSuperAdmin && !$currentUser->isMasterSuperAdmin) {
            return redirect()->back()->withErrors(['You can\'t edit Master super admin.']);
        }

        //Fill Profile Details
        $user->loadProfileFields();

        $roleOptions = Role::getRoleOptions();
        $storeOptions = Store::getStoreOptions();

        return view('backend.user.edit', [
            'user' => $user,
            'roleOptions' => $roleOptions,
            'storeOptions' => $storeOptions
        ]);
    }

    public function update(UserFormRequest $request, $id)
    {
        $user = User::findOrFail($id);

        $accountData = [
            'email' => $request->input('email'),
            'status' => $request->input('status'),
        ];

        if($request->has('password')){
            $accountData['password'] = bcrypt($request->input('password'));
        }

        $user->fill($accountData);
        $user->save();
        $user->saveProfile($request->input('profile'));

        $user->roles()->sync([$request->input('role')]);
        $user->stores()->sync($request->input('stores', []));

        return redirect($request->get('backUrl', route('backend.user.index')))->with('success', ['User is successfully updated.']);
    }

    public function delete($id)
    {
        $currentUser = Auth::user();
        $user = User::findOrFail($id);

        $name = 'User ' . $user->fullName;

        $errors = [];

        if($currentUser->id == $user->id){
            $errors[] = 'You can\'t delete yourself.';
        }elseif($user->isMasterSuperAdmin){
            $errors[] = 'You can\'t delete Master super admin.';
        }

        if($errors){
            return redirect()->back()->withErrors($errors);
        }

        $user->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }
}