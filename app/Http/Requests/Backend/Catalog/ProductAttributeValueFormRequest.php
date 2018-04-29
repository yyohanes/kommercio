<?php

namespace Kommercio\Http\Requests\Backend\Catalog;

use Kommercio\Http\Requests\Request;

class ProductAttributeValueFormRequest extends Request
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
            'slug' => 'required',
            'sort_order' => 'nullable|numeric',
        ];

        return $rules;
    }

    public function all($keys = null)
    {
        $attributes = parent::all($keys);

        if(empty($attributes['sort_order'])){
            $attributes['sort_order'] = 0;
        }

        $this->replace($attributes);

        return parent::all($keys);
    }
}
