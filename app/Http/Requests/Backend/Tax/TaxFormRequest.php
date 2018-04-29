<?php

namespace Kommercio\Http\Requests\Backend\Tax;

use Kommercio\Facades\AddressHelper;
use Kommercio\Http\Requests\Request;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Models\Store;

class TaxFormRequest extends Request
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
        $allowedCountries = implode(',', AddressHelper::getCountries(FALSE)->pluck('id')->all());

        $rules = [
            'name' => 'required',
            'rate' => 'required|numeric',
            'currency' => 'nullable|in:'.$allowedCurrencies,
            'store_id' => 'nullable|in:'.$allowedStores,
            'active' => 'boolean',
            'country' => 'nullable|in:'.$allowedCountries
        ];

        return $rules;
    }

    public function all($keys = null)
    {
        $attributes = parent::all($keys);

        if(!$this->filled('currency')){
            $attributes['currency'] = null;
        }
        if(!$this->filled('store_id')){
            $attributes['store_id'] = null;
        }
        if(!$this->filled('active')){
            $attributes['active'] = false;
        }

        $this->replace($attributes);

        return parent::all($keys);
    }
}
