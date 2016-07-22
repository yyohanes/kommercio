<?php

namespace Kommercio\Facades;

use Illuminate\Support\Facades\Facade;

class KommercioAPIHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'kommercio_api_helper';
    }
}