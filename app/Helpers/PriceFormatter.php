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

            $currency = isset($currencies[$currency])?$currencies[$currency]:$activeCurrency;
        }

        return $currency['symbol'].' '.str_replace($currency['decimal_separator'].'00', '',number_format($number, 2, $currency['decimal_separator'], $currency['thousand_separator']));
    }
}