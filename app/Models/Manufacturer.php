<?php

namespace Kommercio\Models;

use Cviebrock\EloquentSluggable\SluggableInterface;
use Cviebrock\EloquentSluggable\SluggableTrait;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Traits\Model\MediaAttachable;

class Manufacturer extends Model implements SluggableInterface
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

    //Relations
    public function getLogoAttribute()
    {
        $qb = $this->media('logo');
        return $qb->first();
    }

    //Static
    public static function getOptions()
    {
        $qb = self::orderBy('created_at', 'DESC');

        $options = $qb->pluck('name', 'id')->all();

        return $options;
    }
}
