<?php

namespace Kommercio\Http\Requests\Backend\Catalog;

use Kommercio\Http\Requests\Request;

class ProductCategoryFormRequest extends Request
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
        $categoryId = $this->route('id');

        $rules = [
            'name' => 'required',
            'parent_id' => 'nullable|integer|not_in:'.$categoryId,
            'slug' => 'required'
        ];

        return $rules;
    }

    public function all($keys = null)
    {
        $attributes = parent::all($keys);

        if(empty($attributes['parent_id'])){
            $attributes['parent_id'] = null;
        }

        if(!isset($attributes['active'])){
            $attributes['active'] = 0;
        }

        $this->replace($attributes);

        return parent::all($keys);
    }
}
