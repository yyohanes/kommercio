<?php

namespace Kommercio\Models;

use Illuminate\Support\Facades\Cache;
use Kommercio\Models\Abstracts\SluggableModel;
use Kommercio\Models\Interfaces\CacheableInterface;
use Kommercio\Models\Interfaces\ProductIndexInterface;
use Kommercio\Traits\Model\MediaAttachable;

class Manufacturer extends SluggableModel implements ProductIndexInterface, CacheableInterface
{
    use MediaAttachable;

    protected $fillable = ['name'];

    //Accessors
    public function getProductCountAttribute()
    {
        if(!$this->relationLoaded('products')){
            $this->load('products');
        }

        return $this->products->count();
    }

    //Relations
    public function products()
    {
        return $this->hasMany('Kommercio\Models\Product');
    }

    //Methods
    public function getLogoAttribute()
    {
        $logo = Cache::rememberForever($this->getTable().'_'.$this->id.'.logo', function(){
            $qb = $this->media('logo');

            return $qb->first();
        });

        return $logo;
    }

    public function getProductIndexType()
    {
        return 'manufacturer';
    }

    public function getProductIndexRows()
    {
        $rows = collect([$this]);

        return $rows;
    }

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'name',
                'onUpdate' => TRUE
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCacheKeys()
    {
        $tableName = $this->getTable();

        $keys = [
            $tableName.'_'.$this->id.'.logo'
        ];

        return $keys;
    }

    //Static
    public static function getOptions()
    {
        $qb = self::orderBy('created_at', 'DESC');

        $options = $qb->pluck('name', 'id')->all();

        return $options;
    }
}
