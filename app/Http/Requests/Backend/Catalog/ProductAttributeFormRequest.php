<?php

namespace Kommercio\Http\Requests\Backend\Catalog;

use Kommercio\Http\Requests\Request;

class ProductAttributeFormRequest extends Request
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
            'sort_order' => 'numeric',
        ];

        return $rules;
    }
}