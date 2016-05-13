<?php

namespace Kommercio\Http\Requests\Backend\PaymentMethod;

use Kommercio\Http\Requests\Request;

class PaymentMethodFormRequest extends Request
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
        $rules = [
            'name' => 'required',
            'class' => 'required',
        ];

        return $rules;
    }
}