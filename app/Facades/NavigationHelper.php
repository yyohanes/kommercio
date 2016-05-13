<?php

namespace Kommercio\Facades;

use Illuminate\Support\Facades\Facade;

class NavigationHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'navigation_helper';
    }
}