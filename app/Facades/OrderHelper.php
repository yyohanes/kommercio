<?php

namespace Kommercio\Facades;

use Illuminate\Support\Facades\Facade;

class OrderHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'order_helper';
    }
}