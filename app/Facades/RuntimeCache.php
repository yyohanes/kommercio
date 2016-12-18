<?php

namespace Kommercio\Facades;

use Illuminate\Support\Facades\Facade;

class RuntimeCache extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'runtime_cache';
    }
}