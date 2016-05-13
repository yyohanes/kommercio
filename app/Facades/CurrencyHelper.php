<?php

namespace Kommercio\Facades;

use Illuminate\Support\Facades\Facade;

class CurrencyHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'currency_helper';
    }
}