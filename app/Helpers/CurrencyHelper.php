<?php

namespace Kommercio\Helpers;

class CurrencyHelper
{
    public function getDefaultCurrency()
    {
        return config('project.default_currency');
    }

    public function getCurrency($currency)
    {
        return config('project.currencies.'.$currency);
    }

    public function getCurrentCurrency()
    {
        $currentCurrency = $this->getCurrency($this->getDefaultCurrency());

        return $currentCurrency;
    }

    public function getActiveCurrencies()
    {
        $currencies = [];

        foreach(config('project.active_currencies', []) as $activeCurrency){
            $currencies[$activeCurrency] = $this->getCurrency($activeCurrency);
        }

        if(empty($currencies)){
            $currencies = config('project.currencies');
        }

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

    public function convert($amount, $from_currency, $to_currency=null, $currencyRate=1)
    {
        if(!$to_currency){
            $to_currency = $this->getCurrentCurrency()['code'];
        }

        if($from_currency != $to_currency){

        }

        return $amount * $currencyRate;
    }
}