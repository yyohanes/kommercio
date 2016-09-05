<?php

namespace Kommercio\Http\Requests\Backend\Order;

use Kommercio\Facades\ProjectHelper;
use Kommercio\Http\Requests\Request;
use Kommercio\Models\Order\OrderLimit;

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

        if($this->route('id')){
            $model = OrderLimit::findOrFail($this->route('id'));
            $type = $model->type;
        }else{
            $type = $this->route('type');
        }

        switch($type){
            case OrderLimit::TYPE_PRODUCT_CATEGORY:
                $existTable = 'product_categories';
                break;
            default:
                $existTable = 'products';
                break;
        }

        $rules = [
            'items' => 'required',
            'items.*' => 'exists:'.$existTable.',id',
            'limit_type' => 'required|in:'.$allowedLimitTypeOptions,
            'limit' => 'required|numeric|min:0',
            'date_from' => 'date_format:Y-m-d H:i',
            'date_to' => 'date_format:Y-m-d H:i',
            'dayRules' => 'required',
            'dayRules.*.days.*' => 'in:'.implode(',', array_keys(ProjectHelper::getDaysOptions()))
        ];

        return $rules;
    }

    public function all()
    {
        $attributes = parent::all();

        if(!$this->has('store_id')){
            $attributes['store_id'] = null;
        }

        if(!$this->has('active')){
            $attributes['active'] = 0;
        }

        $this->replace($attributes);

        return parent::all();
    }
}