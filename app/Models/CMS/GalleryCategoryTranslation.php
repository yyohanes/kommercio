<?php

namespace Kommercio\Models\CMS;

use Cviebrock\EloquentSluggable\SluggableTrait;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Traits\Model\MediaAttachable;

class GalleryCategoryTranslation extends Model
{
    use SluggableTrait;

    public $timestamps = FALSE;

    protected $sluggable = [
        'build_from' => 'name',
        'save_to'    => 'slug',
        'on_update' => TRUE,
    ];
}
