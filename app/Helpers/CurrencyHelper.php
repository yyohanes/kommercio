<?php

namespace Kommercio\Helpers;

use Illuminate\Support\Facades\DB;
use Kommercio\Facades\RuntimeCache as RuntimeCacheFacade;
use Kommercio\Models\CurrencyRate;

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

        $rate = 1;

        if($from_currency != $to_currency){
            $rate = RuntimeCacheFacade::getOrSet('currency_rates.'.$from_currency.'_'.$to_currency, function() use ($from_currency, $to_currency){
                $rate = CurrencyRate::getRate($from_currency, $to_currency);

                return $rate;
            });
        }

        return $amount * $rate;
    }
}