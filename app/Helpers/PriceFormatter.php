<?php

namespace Kommercio\Helpers;

class PriceFormatter
{
    public function formatNumber($number, $currency=NULL)
    {
        $activeCurrency = \CurrencyHelper::getCurrentCurrency();

        if(empty($currency)){
            $currency = $activeCurrency;
        }else{
            $currencies = \CurrencyHelper::getActiveCurrencies();

            $currency = isset($currencies[$currency])?$currency:$activeCurrency;

            $currency = \CurrencyHelper::getCurrency($currency);
        }

        return $currency['symbol'].' '.number_format($number, 0, $currency['decimal_separator'], $currency['thousand_separator']);
    }
}