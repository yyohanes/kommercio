<?php

namespace Kommercio\Helpers;

use Illuminate\Support\Facades\Lang;

class LanguageHelper
{
    public function getTranslationKey($key)
    {
        $root = 'project::';

        if(!Lang::has($root.$key)){
            $root = '';
        }

        return $root.$key;
    }
}