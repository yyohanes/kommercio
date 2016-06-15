<?php

namespace Kommercio\Facades;

use Illuminate\Support\Facades\Facade;

class LanguageHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'language_helper';
    }
}