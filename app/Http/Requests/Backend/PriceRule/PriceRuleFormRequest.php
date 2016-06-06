<?php

namespace Kommercio\Http\Requests\Backend\PriceRule;

use Kommercio\Facades\CurrencyHelper;
use Kommercio\Http\Requests\Request;
use Kommercio\Models\PriceRule;
use Kommercio\Models\Product;
use Kommercio\Models\Store;

class PriceRuleFormRequest extends Request
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
        $allowedModificationType = implode(',', array_keys(PriceRule::getModificationTypeOptions()));

        $rules = [
            'price_rule.price' => 'required_without:price_rule.modification|numeric|min:0',
            'price_rule.modification' => 'required_without:price_rule.price|numeric',
            'price_rule.modification_type' => 'in:'.$allowedModificationType,
            'price_rule.store_id' => 'in:'.$allowedStores,
            'price_rule.currency' => 'in:'.$allowedCurrencies,
            'price_rule.active_date_from' => 'date_format:Y-m-d H:i',
            'price_rule.active_date_to' => 'date_format:Y-m-d H:i',
        ];

        if($this->route('product_id')){
            $product = Product::findOrFail($this->route('product_id'));
            $allowedVariations = $product->variations->pluck('id')->all();

            if($allowedVariations){
                $rules['price_rule.variation_id'] = 'in:'.implode(',', $allowedVariations);
            }
        }else{
            $rules['price_rule.name'] = 'required';
        }

        return $rules;
    }

    public function all()
    {
        $attributes = parent::all();

        //Remove empty price rule options
        if($this->has('options')){
            foreach($attributes['options'] as $idx=>$optionGroup){
                $empty = TRUE;

                foreach($optionGroup as $option){
                    if(!empty($option)){
                        $empty = FALSE;
                        break;
                    }
                }

                if($empty){
                    unset($attributes['options'][$idx]);
                }
            }
        }

        if(!$this->has('price_rule')){
            $attributes['price_rule'] = $attributes;
        }

        $this->replace($attributes);

        if(!$this->has('price_rule.price')){
            $attributes['price_rule']['price'] = null;
        }
        if(!$this->has('price_rule.modification')){
            $attributes['price_rule']['modification'] = null;
        }
        if(!$this->has('price_rule.variation_id')){
            $attributes['price_rule']['variation_id'] = null;
        }
        if(!$this->has('price_rule.store_id')){
            $attributes['price_rule']['store_id'] = null;
        }
        if(!$this->has('price_rule.active')){
            $attributes['price_rule']['active'] = false;
        }

        if(!$this->has('price_rule.is_discount')){
            $attributes['price_rule']['is_discount'] = false;
        }

        $this->replace($attributes);

        return parent::all();
    }
}