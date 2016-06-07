<?php

namespace Kommercio\Facades;

use Illuminate\Support\Facades\Facade;

class EmailHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'email_helper';
    }
}