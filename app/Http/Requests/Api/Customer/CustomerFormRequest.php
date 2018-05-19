<?php

namespace Kommercio\Http\Requests\Api\Customer;

use Kommercio\Models\Customer;
use Kommercio\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomerFormRequest extends \Illuminate\Foundation\Http\FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        $rules = static::getRules($this);

        return $rules;
    }

    public static function getRules(Request $request) {
        $user = null;
        $statusAllowedOptions = User::getStatusOptions();
        $saluteAllowedOptions = Customer::getSaluteOptions();

        $customerId = $request->route('id');

        if ($customerId) {
            $customer = Customer::findById($customerId);

            if (!$customer)
                throw new NotFoundHttpException('Customer not found');

            $user = $customer->user;
        }

        $rules = [
            'email' => [
                'email',
                'required_with:_create_account',
                'unique:users,email' . ($user ? ',' . $user->id :null)
            ],
            'full_name' => ['required'],
            'phone_number' => ['required'],
            'home_phone' => ['nullable'],
            'salute' => [
                'nullable',
                'in:' . implode(',', $saluteAllowedOptions),
            ],
            'birthday' => [
                'nullable',
                'date_format:Y-m-d',
            ],
            'user.password' => [
                'required_with:_create_account',
                'min:6',
            ],
            'user.status' => [
                'required_with:user._create_account',
                'in:' . implode(',', $statusAllowedOptions),
            ],
            'signup_newsletter' => [
                'nullable',
                'boolean',
            ],
        ];

        if ($user) {
            $rules['user.password'] += [
                'nullable',
            ];
        }

        return $rules;
    }
}
