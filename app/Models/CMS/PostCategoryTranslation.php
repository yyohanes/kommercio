<?php

namespace Kommercio\Models\CMS;

use Kommercio\Models\Abstracts\SluggableModel;
use Kommercio\Traits\Model\MediaAttachable;

class PostCategoryTranslation extends SluggableModel
{
    use MediaAttachable;

    public $fillable = [
        'name',
        'body',
        'slug',
        'meta_title',
        'meta_description',
        'locale',
    ];

    public $timestamps = FALSE;

    //Relations
    public function images()
    {
        return $this->media('image')->where('locale', $this->locale);
    }
}
