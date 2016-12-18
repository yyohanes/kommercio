<?php

namespace Kommercio\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyRate extends Model
{
    protected $fillable = ['from_currency', 'to_currency', 'rate'];

    //Statics
    public static function getRate($from, $to)
    {
        $rate = self::where('from_currency', $from)->where('to_currency', $to)->first();

        if($rate){
            return $rate->rate;
        }

        return false;
    }
}
