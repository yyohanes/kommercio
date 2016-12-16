<?php

namespace Kommercio\Http\Requests\Backend\Customer;

use Kommercio\Http\Requests\Request;
use Kommercio\Models\RewardPoint\Reward;

class RewardFormRequest extends Request
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
        $allowedTypes = implode(',', array_keys(Reward::getTypeOptions()));

        $rules = [
            'name' => 'required',
            'type' => 'required|in:'.$allowedTypes,
            'store_id' => 'exists:stores,id',
            'active_date_from' => 'date_format:Y-m-d H:i',
            'active_date_to' => 'date_format:Y-m-d H:i',
            'points' => 'required|min:0',
        ];

        if($this->input('type') == Reward::TYPE_ONLINE_COUPON){
            $rules += [
                'cart_price_rule_id' => 'required|exists:cart_price_rules,id',
            ];
        }

        return $rules;
    }

    public function all()
    {
        $attributes = parent::all();

        if(!$this->has('store_id')){
            $attributes['store_id'] = null;
        }
        if(!$this->has('active')){
            $attributes['active'] = false;
        }

        $this->replace($attributes);

        return parent::all();
    }
}