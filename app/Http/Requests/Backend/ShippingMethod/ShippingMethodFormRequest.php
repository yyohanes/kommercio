<?php

namespace Kommercio\Http\Requests\Backend\ShippingMethod;

use Kommercio\Http\Requests\Request;

class ShippingMethodFormRequest extends Request
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
            'taxable' => 'boolean',
            'store_scope' => 'in:all,selected',
            'stores' => 'required_if:store_scope,selected',
            'stores.*' => 'nullable|exists:stores,id'
        ];

        return $rules;
    }

    public function all($keys = null)
    {
        $attributes = parent::all($keys);

        if(!$this->filled('taxable')){
            $attributes['taxable'] = 0;
        }

        if(!isset($attributes['active'])){
            $attributes['active'] = 0;
        }

        $this->replace($attributes);

        return parent::all($keys);
    }
}
