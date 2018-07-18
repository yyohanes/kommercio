<?php

namespace Kommercio\Models\CMS;

use Illuminate\Support\Facades\Cache;
use Kommercio\Models\Abstracts\SluggableModel;
use Kommercio\Models\Interfaces\CacheableInterface;

class BannerGroup extends SluggableModel implements CacheableInterface
{
    public $fillable = ['name', 'slug', 'description'];

    //Relations
    public function banners()
    {
        return $this->hasMany('Kommercio\Models\CMS\Banner')->orderBy('sort_order', 'ASC');
    }

    //Methods
    public function getBanners()
    {
        $activeBanners = Cache::rememberForever($this->getTable().'_'.$this->id.'_active_banners', function() {
            return $this->banners()->active()->get();
        });
        return $activeBanners;
    }

    public function getCacheKeys()
    {
        $table = $this->getTable();

        return [
            $table . '_' . $this->slug,
            $table . '_' . $this->id,
            $table . '_' . $this->id . '_active_banners',
        ];
    }

    // Statics
    public static function findById($id)
    {
        $tableName = (new static)->getTable();
        $bannerGroup = Cache::remember($tableName. '_' . $id, 3600, function() use ($id) {
            return static::find($id);
        });

        return $bannerGroup;
    }

    public static function getBySlug($slug)
    {
        $tableName = (new static)->getTable();
        $bannerGroup = Cache::remember($tableName. '_' . $slug, 3600, function() use ($slug) {
            return self::where('slug', $slug)->first();
        });

        return $bannerGroup;
    }
}
