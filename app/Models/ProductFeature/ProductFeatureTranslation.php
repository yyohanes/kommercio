<?php

namespace Kommercio\Models\ProductFeature;

use Kommercio\Models\Abstracts\SluggableModel;

class ProductFeatureTranslation extends SluggableModel
{
    public $fillable = [
        'name',
        'slug',
        'locale',
    ];

    public $timestamps = FALSE;
}
