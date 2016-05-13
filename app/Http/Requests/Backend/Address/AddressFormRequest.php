<?php

namespace Kommercio\Http\Requests\Backend\Address;

use Kommercio\Http\Requests\Request;

class AddressFormRequest extends Request
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
        $type = $this->route('type');

        $rules = [
            'name' => 'required',
            'active' => 'required|boolean',
            'parent_id' => 'required|integer'
        ];

        switch($type){
            case 'state':
                break;
            case 'city':
                break;
            case 'district':
                break;
            case 'area':
                break;
            default:
                $rules['iso_code'] = 'required|size:2';
                $rules['country_code'] = 'required';
                unset($rules['parent_id']);
                break;
        }

        return $rules;
    }

    public function all()
    {
        $attributes = parent::all();

        if(!$this->has('active')){
            $attributes['active'] = 0;
        }

        if(!$this->has('has_descendant')){
            $attributes['has_descendant'] = 0;
        }

        $this->replace($attributes);

        return parent::all();
    }
}