<?php

namespace Kommercio\Http\Requests\Backend\Catalog;

use Kommercio\Http\Requests\Request;

class ProductCompositeFormRequest extends Request
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
            'minimum' => 'required|numeric|min:0',
            'maximum' => 'required|numeric|min:0',
            'composite_product' => 'required_without:product_category|array',
            'composite_product.*' => 'nullable|numeric|exists:products,id',
            'product_category' => 'required_without:composite_product|array',
            'product_category.*' => 'nullable|numeric|exists:product_categories,id',
            'default_product_product.*' => 'nullable|numeric|exists:products,id',
            'default_product_quantity.*' => 'nullable|numeric|min:0',
            'total_default_product_quantity' => 'nullable|numeric|max:' . $this->input('maximum')
        ];

        return $rules;
    }

    public function all()
    {
        $attributes = parent::all();

        if(!isset($attributes['free'])){
            $attributes['free'] = 0;
        }

        // Calculate for validation
        $attributes['total_default_product_quantity'] = 0;
        foreach ($this->input('default_product_quantity', []) as $defaultQty) {
            $attributes['total_default_product_quantity'] += $defaultQty;
        }

        $this->replace($attributes);

        return parent::all();
    }
}