<?php

namespace Kommercio\Http\Requests\Backend\PriceRule;

use Kommercio\Facades\CurrencyHelper;
use Kommercio\Http\Requests\Request;
use Kommercio\Models\PriceRule\CartPriceRule;
use Kommercio\Models\Store;

class CartPriceRuleFormRequest extends Request
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
        $allowedModificationType = implode(',', array_keys(CartPriceRule::getModificationTypeOptions()));
        $allowedOfferTypeType = implode(',', array_keys(CartPriceRule::getOfferTypeOptions()));

        $rules = [
            'name' => 'required',
            'coupon_code' => 'unique:cart_price_rules,coupon_code'.($this->route('id')?','.$this->route('id'):''),
            'offer_type' => 'nullable|in:'.$allowedOfferTypeType,
            'store_id' => 'nullable|in:'.$allowedStores,
            'currency' => 'nullable|in:'.$allowedCurrencies,
            'active_date_from' => 'nullable|date_format:Y-m-d H:i',
            'active_date_to' => 'nullable|date_format:Y-m-d H:i',
            'max_usage' => 'min:0',
            'max_usage_per_customer' => 'min:0',
            'minimum_subtotal' => 'min:0',
        ];

        if($this->input('offer_type') != CartPriceRule::OFFER_TYPE_FREE_SHIPPING){
            $rules += ['price' => 'required_without:modification|numeric|min:0',
                'modification' => 'required_without:price|numeric',
                'modification_type' => 'nullable|in:'.$allowedModificationType
            ];
        }

        return $rules;
    }

    public function all()
    {
        $attributes = parent::all();

        if(!$this->has('price')){
            $attributes['price'] = null;
        }
        if(!$this->has('coupon_code')){
            $attributes['coupon_code'] = null;
        }
        if(!$this->has('max_usage')){
            $attributes['max_usage'] = null;
        }
        if(!$this->has('max_usage_per_customer')){
            $attributes['max_usage_per_customer'] = null;
        }
        if(!$this->has('modification')){
            $attributes['modification'] = null;
        }
        if(!$this->has('store_id')){
            $attributes['store_id'] = null;
        }
        if(!$this->has('currency')){
            $attributes['currency'] = null;
        }
        if(!$this->has('active')){
            $attributes['active'] = false;
        }

        $this->replace($attributes);

        return parent::all();
    }
}