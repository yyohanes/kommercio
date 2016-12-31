<?php

namespace Kommercio\Models;

use Cviebrock\EloquentSluggable\SluggableInterface;
use Cviebrock\EloquentSluggable\SluggableTrait;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Interfaces\ProductIndexInterface;
use Kommercio\Traits\Model\MediaAttachable;

class Manufacturer extends Model implements SluggableInterface, ProductIndexInterface
{
    use MediaAttachable, SluggableTrait;

    protected $fillable = ['name'];
    protected $sluggable = [
        'build_from' => 'name',
        'save_to'    => 'slug',
        'on_update' => TRUE,
    ];

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
        $qb = $this->media('logo');
        return $qb->first();
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

    //Static
    public static function getOptions()
    {
        $qb = self::orderBy('created_at', 'DESC');

        $options = $qb->pluck('name', 'id')->all();

        return $options;
    }
}
