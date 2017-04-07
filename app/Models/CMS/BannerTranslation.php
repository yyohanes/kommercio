<?php

namespace Kommercio\Models\CMS;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Traits\Model\MediaAttachable;

class BannerTranslation extends Model
{
    use MediaAttachable;

    public $timestamps = FALSE;

    //Accessors
    public function getImageAttribute()
    {
        if(!$this->relationLoaded('images')){
            $this->load('images');
        }

        return $this->images->first();
    }

    //Relations
    public function images()
    {
        return $this->media('image')->where('locale', $this->locale);
    }

    public function videos()
    {
        return $this->media('video')->where('locale', $this->locale);
    }
}
