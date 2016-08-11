<?php

namespace Kommercio\Http\Requests\Backend\Customer;

use Kommercio\Http\Requests\Request;
use Kommercio\Models\Customer;
use Kommercio\Models\User;

class CustomerFormRequest extends Request
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
        $customer = Customer::find($this->route('id'));
        $user = $customer?$customer->user:null;

        $saluteAllowedOptions = array_keys(Customer::getSaluteOptions());
        $statusAllowedOptions = array_keys(User::getStatusOptions());

        $rules = [
            'profile.email' => 'email|required_with:user.create_account|unique:users,email'.($user?','.$user->id:null),
            'profile.salute' => 'in:'.implode(',', $saluteAllowedOptions),
            'profile.full_name' => 'required',
            'profile.birthday' => 'date_format:Y-m-d',
            'user.password' => 'confirmed|min:6',
            'user.status' => 'required_with:user.create_account|in:'.implode(',', $statusAllowedOptions),
            'store_id' => 'required|integer',
        ];

        if(!$user){
            $rules['user.password'] .= '|required_with:user.create_account|';
        }

        return $rules;
    }
}