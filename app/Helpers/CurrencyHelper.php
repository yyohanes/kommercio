<?php

namespace Kommercio\Helpers;

class CurrencyHelper
{
    public function getDefaultCurrency()
    {
        return config('currency.default_currency');
    }

    public function getCurrency($currency)
    {
        return config('currency.currencies.'.$currency);
    }

    public function getCurrentCurrency()
    {
        $currentCurrency = $this->getCurrency($this->getDefaultCurrency());

        return $currentCurrency;
    }

    public function getActiveCurrencies()
    {
        $currencies = config('currency.currencies');

        return $currencies;
    }

    public function getCurrencyOptions()
    {
        $currencies = $this->getActiveCurrencies();
        $options = [];

        foreach($currencies as $code=>$currency){
            $options[$code] = $currency['iso'];
        }

        return $options;
    }
}