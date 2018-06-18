<?php

namespace Kommercio\Http\Requests\Api\Order;

use Illuminate\Http\Request;

class ShippingMethodsFormRequest extends \Illuminate\Foundation\Http\FormRequest {
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
        $rules = [
            'store_id' => [
                'required',
                'integer',
                'exists:stores,id',
            ],
            'products.*' => [
                'nullable',
                'exists:products,id,deleted_at,NULL',
            ],
            'quantities.*' => [
                'nullable',
                'min:1',
            ],
            'shipping_method' => [
                'nullable',
                'exists:shipping_methods,id',
            ],
            'shipping_option' => [
                'nullable',
            ],
        ];

        return $rules;
    }
}
