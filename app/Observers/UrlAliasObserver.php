<?php

namespace Kommercio\Observers;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Interfaces\UrlAliasInterface;
use Kommercio\Models\UrlAlias;

class UrlAliasObserver
{
    public function saved(Model $model)
    {
        if ($model instanceof UrlAliasInterface) {
            UrlAlias::saveAlias($model->getUrlAlias(), $model);
        }
    }
}