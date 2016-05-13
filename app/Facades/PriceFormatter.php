<?php

namespace Kommercio\Facades;

use Illuminate\Support\Facades\Facade;

class PriceFormatter extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'price_formatter';
    }
}