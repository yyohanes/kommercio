<?php

namespace Kommercio\Http\Requests\Backend\Catalog;

use Kommercio\Http\Requests\Request;
use Kommercio\Models\Product\Configuration\ProductConfiguration;

class ProductConfigurationFormRequest extends Request
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
            'required' => 'boolean'
        ];

        foreach(ProductConfiguration::getTypeRules($this->input('type')) as $ruleKey => $rule){
            $rules[$this->input('type').'.rules.'.$ruleKey] = $rule;
        }

        return $rules;
    }

    public function all()
    {
        $attributes = parent::all();

        if(!$this->has('required')){
            $attributes['required'] = 0;
        }

        $this->replace($attributes);

        return parent::all();
    }
}