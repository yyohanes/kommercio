<?php

namespace Kommercio\Http\Requests\Backend\Warehouse;

use Kommercio\Http\Requests\Request;

class WarehouseFormRequest extends Request
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
        ];

        return $rules;
    }

    public function all()
    {
        $attributes = parent::all();

        foreach($attributes['location'] as $idx => $location){
            if(!$location && $idx !== 'custom_city'){
                unset($attributes['location'][$idx]);
            }
        }

        $this->replace($attributes);

        return parent::all();
    }
}
