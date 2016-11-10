<?php

namespace Kommercio\Models\CMS;

use Cviebrock\EloquentSluggable\SluggableInterface;
use Cviebrock\EloquentSluggable\SluggableTrait;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Traits\Model\MediaAttachable;

class GalleryTranslation extends Model implements SluggableInterface
{
    use MediaAttachable, SluggableTrait;

    public $timestamps = FALSE;

    protected $sluggable = [
        'build_from' => 'name',
        'save_to'    => 'slug',
        'on_update' => TRUE,
    ];

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
