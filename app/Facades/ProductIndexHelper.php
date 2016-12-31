<?php

namespace Kommercio\Facades;

use Illuminate\Support\Facades\Facade;

class ProductIndexHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'product_index_helper';
    }
}