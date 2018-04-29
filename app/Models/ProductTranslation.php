<?php

namespace Kommercio\Models;

use Illuminate\Support\Facades\Cache;
use Kommercio\Models\Abstracts\SluggableModel;
use Kommercio\Models\Interfaces\CacheableInterface;
use Kommercio\Traits\Model\AuthorSignature;
use Kommercio\Traits\Model\MediaAttachable;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;

class ProductTranslation extends SluggableModel implements AuthorSignatureInterface, CacheableInterface
{
    use AuthorSignature, MediaAttachable;

    public $fillable = [
        'name',
        'description_short',
        'description',
        'slug',
        'meta_title',
        'meta_description',
        'locale',
    ];

    public $timestamps = FALSE;

    // Methods

    /**
     * @inheritdoc
     */
    public function getCacheKeys()
    {
        $tableName = $this->getTable();

        $keys = [
            $tableName.'_'.$this->id.'_'.$this->locale.'.thumbnails',
            $tableName.'_'.$this->id.'_'.$this->locale.'.images',
        ];

        return $keys;
    }

    // Accessors
    public function getThumbnailAttribute()
    {
        return $this->thumbnails->first();
    }

    public function getThumbnailsAttribute()
    {
        $thumbnails = Cache::rememberForever($this->getTable().'_'.$this->id.'_'.$this->locale.'.thumbnails', function(){
            return $this->media('thumbnail')->where('locale', $this->locale)->get();
        });

        return $thumbnails;
    }

    public function getImagesAttribute()
    {
        $images = Cache::rememberForever($this->getTable().'_'.$this->id.'_'.$this->locale.'.images', function(){
            return $this->media('image')->where('locale', $this->locale)->get();
        });

        return $images;
    }
}
