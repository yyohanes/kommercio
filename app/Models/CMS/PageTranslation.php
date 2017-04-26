<?php

namespace Kommercio\Models\CMS;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Abstracts\SluggableModel;
use Kommercio\Traits\Model\MediaAttachable;

class PageTranslation extends SluggableModel
{
    use MediaAttachable;

    public $timestamps = FALSE;

    //Methods


    //Relations
    public function images()
    {
        return $this->media('image')->where('locale', $this->locale);
    }
}
