<?php

namespace Kommercio\Models\ProductAttribute;

use Kommercio\Models\Abstracts\SluggableModel;

class ProductAttributeTranslation extends SluggableModel
{
    public $fillable = [
        'name',
        'slug',
        'locale',
    ];

    public $timestamps = FALSE;
}
