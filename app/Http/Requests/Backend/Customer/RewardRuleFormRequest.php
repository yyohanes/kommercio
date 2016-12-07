<?php

namespace Kommercio\Http\Requests\Backend\Customer;

use Kommercio\Facades\CurrencyHelper;
use Kommercio\Http\Requests\Request;
use Kommercio\Models\RewardPoint\RewardRule;
use Kommercio\Models\Store;

class RewardRuleFormRequest extends Request
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
        $allowedCurrencies = implode(',', array_keys(CurrencyHelper::getActiveCurrencies()));
        $allowedStores = implode(',', array_keys(Store::getStoreOptions()));
        $allowedTypes = implode(',', array_keys(RewardRule::getTypeOptions()));

        $rules = [
            'name' => 'required',
            'type' => 'required|in:'.$allowedTypes,
            'store_id' => 'in:'.$allowedStores,
            'currency' => 'in:'.$allowedCurrencies,
            'active_date_from' => 'date_format:Y-m-d H:i',
            'active_date_to' => 'date_format:Y-m-d H:i',
            'reward' => 'required|min:0',
        ];

        if($this->input('type') == RewardRule::TYPE_PER_ORDER){
            $rules += [
                'rule.order_step_amount' => 'required|numeric|min:0',
                'rule.include_shipping' => 'boolean',
                'rule.include_tax' => 'boolean',
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
        if(!$this->has('currency')){
            $attributes['currency'] = null;
        }
        if(!$this->has('active')){
            $attributes['active'] = false;
        }
        if(!$this->has('member')){
            $attributes['member'] = false;
        }

        if($this->input('type') == RewardRule::TYPE_PER_ORDER){
            if(!$this->has('rule.include_shipping')){
                $attributes['rule']['include_shipping'] = false;
            }

            if(!$this->has('rule.include_tax')){
                $attributes['rule']['include_tax'] = false;
            }
        }

        $this->replace($attributes);

        return parent::all();
    }
}