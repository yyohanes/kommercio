<?php

namespace Kommercio\Facades;

use Illuminate\Support\Facades\Facade;

class FrontendHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'frontend_helper';
    }
}