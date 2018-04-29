<?php

namespace Kommercio\Http\Requests\Backend\Order;

use Kommercio\Facades\ProjectHelper;
use Kommercio\Http\Requests\Request;
use Kommercio\Models\Order\OrderLimit;
use Kommercio\Models\Store;

class OrderLimitFormRequest extends Request
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
        $allowedLimitTypeOptions = implode(',', array_keys(OrderLimit::getLimitTypeOptions()));

        $rules = [
            'limit_type' => 'required|in:'.$allowedLimitTypeOptions,
            'limit' => 'required|numeric|min:0',
            'date_from' => 'nullable|date_format:Y-m-d H:i',
            'date_to' => 'nullable|date_format:Y-m-d H:i',
            'dayRules' => 'required',
            'dayRules.*.days.*' => 'nullable|in:'.implode(',', array_keys(ProjectHelper::getDaysOptions())),
            'store_id' => 'nullable|in:'.implode(',', array_keys(Store::getStoreOptions()))
        ];

        if($this->input('type') == OrderLimit::TYPE_PRODUCT_CATEGORY){
            $rules += [
                'categories' => 'required:products',
                'categories.*' => 'exists:product_categories,id'
            ];
        }else{
            $rules += [
                'products' => 'required_without:products',
                'products.*' => 'exists:products,id',
                'categories' => 'required_without:products',
                'categories.*' => 'exists:product_categories,id'
            ];
        }

        return $rules;
    }

    public function all($keys = null)
    {
        $attributes = parent::all($keys);

        if(!$this->filled('store_id')){
            $attributes['store_id'] = null;
        }

        if(!$this->filled('active')){
            $attributes['active'] = 0;
        }

        if(!$this->filled('backoffice')){
            $attributes['backoffice'] = 0;
        }

        $this->replace($attributes);

        return parent::all($keys);
    }
}
