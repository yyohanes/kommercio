<?php

namespace Kommercio\Http\Requests\Backend\User;

use Kommercio\Http\Requests\Request;
use Kommercio\Models\Role\Role;
use Kommercio\Models\Store;
use Kommercio\Models\User;

class UserFormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user_id = $this->route('id');

        $statusAllowedOptions = array_keys(User::getStatusOptions());
        $storeAllowedOptions = array_keys(Store::getStoreOptions());
        $roleAllowedOptions = array_keys(Role::getRoleOptions());

        $rules = [
            'email' => 'email|unique:users,email'.($user_id?','.$user_id:null),
            'profile.full_name' => 'required',
            'profile.phone_number' => '',
            'password' => ($user_id?'':'required|').'confirmed|min:6',
            'status' => 'required|in:'.implode(',', $statusAllowedOptions),
            'role' => 'required|in:'.implode(',', $roleAllowedOptions),
            'stores.*' => 'in:'.implode(',', $storeAllowedOptions),
        ];

        return $rules;
    }
}