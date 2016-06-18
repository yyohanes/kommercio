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

    public function calculateRounding($before, $after)
    {
        return $this->round($after - $before);
    }

    public function round($amount, $precision=null, $roundMode='round')
    {
        if(!$precision){
            $precision = config('project.line_item_total_precision');
        }

        $mult = pow(10, $precision);

        switch($roundMode){
            case 'floor':
                $roundFunction = 'floor';
                break;
            case 'ceil':
                $roundFunction = 'ceil';
                break;
            default:
                $roundFunction = 'round';
                break;
        }

        return $roundFunction($amount * $mult) / $mult;
    }
}