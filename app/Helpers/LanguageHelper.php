<?php

namespace Kommercio\Helpers;

use Illuminate\Support\Facades\Lang;

class LanguageHelper
{
    public function getTranslationKey($key)
    {
        $root = '';

        if(Lang::has('project.'.$key)){
            $root = 'project::';
        }

        return $root.$key;
    }
}