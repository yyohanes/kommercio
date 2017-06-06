<?php

namespace Kommercio\Models\CMS;

use Kommercio\Models\Abstracts\SluggableModel;
use Kommercio\Traits\Model\MediaAttachable;

class PostCategoryTranslation extends SluggableModel
{
    use MediaAttachable;

    public $timestamps = FALSE;

    //Relations
    public function images()
    {
        return $this->media('image')->where('locale', $this->locale);
    }
}
