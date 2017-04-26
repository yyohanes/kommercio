<?php

namespace Kommercio\Models;

use Kommercio\Models\Abstracts\SluggableModel;
use Kommercio\Traits\Model\AuthorSignature;
use Kommercio\Traits\Model\MediaAttachable;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;

class ProductTranslation extends SluggableModel implements AuthorSignatureInterface
{
    use AuthorSignature, MediaAttachable;

    public $timestamps = FALSE;

    //Methods

    //Accessors
    public function getThumbnailAttribute()
    {
        if(!$this->relationLoaded('thumbnails')){
            $this->load('thumbnails');
        }

        return $this->thumbnails->first();
    }

    //Relations
    public function thumbnails()
    {
        return $this->media('thumbnail')->where('locale', $this->locale);
    }

    public function images()
    {
        return $this->media('image')->where('locale', $this->locale);
    }
}
