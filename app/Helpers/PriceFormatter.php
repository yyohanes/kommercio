<?php

namespace Kommercio\Helpers;

use Kommercio\Facades\ProjectHelper as ProjectHelperFacade;
use Kommercio\Models\Store;

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

    public function getTaxPrice($price, $options = [])
    {
        return $price + $this->calculateTax($price, $options);
    }

    public function calculateTax($price, $options = [])
    {
        $store = null;

        if(isset($options['store_id'])){
            $store = Store::find($options['store_id']);
        }elseif(isset($options['store'])){
            $store = Store::find($options['store']);
        }

        if(!$store){
            $store = ProjectHelperFacade::getActiveStore();
        }

        $taxTotal = 0;
        $taxes = $store->getTaxes();

        foreach($taxes as $tax){
            $taxValue = [
                'net' => 0,
                'gross' => 0,
                'rate_total' => 0
            ];

            $taxValue['gross'] = $this->round($tax->calculateTax($price));
            $taxValue['net'] = $this->round($taxValue['gross']);
            $taxValue['rate_total'] += $tax->rate;

            $taxTotal += $taxValue['net'];
        }

        return $taxTotal;
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