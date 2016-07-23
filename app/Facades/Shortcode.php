<?php

namespace Kommercio\Facades;

use Illuminate\Support\Facades\Facade;

class Shortcode extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'shortcode_manager';
    }
}