<?php

namespace Kommercio\Models\CMS;

use Kommercio\Models\Abstracts\SluggableModel;

class GalleryCategoryTranslation extends SluggableModel
{
    public $fillable = [
        'name',
        'body',
        'slug',
        'meta_title',
        'meta_description',
        'locale',
    ];

    public $timestamps = FALSE;
}
