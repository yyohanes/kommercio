<?php

namespace Kommercio\Models\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Kommercio\Models\Interfaces\CacheableInterface;
use Kommercio\Traits\Model\MediaAttachable;

class BannerTranslation extends Model implements CacheableInterface
{
    use MediaAttachable;

    public $fillable = [
        'name',
        'body',
        'locale',
    ];

    public $timestamps = FALSE;

    private $_cachedRelationResults;

    public function getCacheKeys()
    {
        $tableName = $this->getTable();
        $keys = [
            $tableName.'_'.$this->id.'_images',
            $tableName.'_'.$this->id.'_videos',
        ];

        return $keys;
    }

    //Accessors
    public function getImageAttribute()
    {
        return $this->images->first();
    }

    public function getImagesAttribute()
    {
        if (!isset($this->_cachedRelationResults['_images'])) {
            $this->_cachedRelationResults['_images'] = Cache::rememberForever($this->getTable().'_'.$this->id.'_images', function () {
                return $this->mediaImages;
            });
        }

        return $this->_cachedRelationResults['_images'];
    }

    public function getVideosAttribute()
    {
        if (!isset($this->_cachedRelationResults['_videos'])) {
            $this->_cachedRelationResults['_videos'] = Cache::rememberForever($this->getTable().'_'.$this->id.'_videos', function () {
                return $this->mediaVideos;
            });
        }

        return $this->_cachedRelationResults['_videos'];
    }

    //Relations
    public function mediaImages()
    {
        return $this->media('image')->where('locale', $this->locale);
    }

    public function mediaVideos()
    {
        return $this->media('video')->where('locale', $this->locale);
    }
}
