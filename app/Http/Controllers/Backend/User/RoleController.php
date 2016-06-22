<?php

namespace Kommercio\Http\Controllers\Backend\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Requests\Backend\User\RoleFormRequest;
use Kommercio\Models\Role\Role;

class RoleController extends Controller{
    public function index(Request $request)
    {
        $qb = Role::with('users')->orderBy('created_at' , 'DESC');

        $roles = $qb->get();

        return view('backend.user.role.index', [
            'roles' => $roles
        ]);
    }

    public function create()
    {
        $role = new Role();

        return view('backend.user.role.create', [
            'role' => $role,
        ]);
    }

    public function store(RoleFormRequest $request)
    {
        $role = Role::create([
            'name' => $request->input('name')
        ]);

        $permissions = array_keys($request->input('permissions', []));
        $role->savePermissions($permissions);

        return redirect($request->get('backUrl', route('backend.user.role.index')))->with('success', ['New Role is successfully created.']);
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);

        if($role->id == 1) {
            return redirect()->back()->withErrors(['You can\'t edit master role.']);
        }

        return view('backend.user.role.edit', [
            'role' => $role,
        ]);
    }

    public function update(RoleFormRequest $request, $id)
    {
        $role = Role::findOrFail($id);
        $role->fill($request->all());

        $permissions = array_keys($request->input('permissions', []));
        $role->savePermissions($permissions);

        return redirect($request->get('backUrl', route('backend.user.role.index')))->with('success', ['Role is successfully updated.']);
    }

    public function delete($id)
    {
        $role = Role::findOrFail($id);

        $name = 'Role ' . $role->name;

        $errors = [];

        if($role->id == 1){
            $errors[] = 'You can\'t delete master role.';
        }

        if($errors){
            return redirect()->back()->withErrors($errors);
        }

        $role->delete();

        return redirect()->back()->with('success', [$name.' has been deleted.']);
    }
}