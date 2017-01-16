<?php

namespace Kommercio\Models\Product\Composite;

use Cviebrock\EloquentSluggable\SluggableInterface;
use Cviebrock\EloquentSluggable\SluggableTrait;
use Illuminate\Database\Eloquent\Model;

class ProductCompositeGroup extends Model implements SluggableInterface
{
    use SluggableTrait;

    protected $fillable = ['name', 'slug'];
    protected $sluggable = [
        'build_from' => 'name',
        'save_to' => 'slug',
    ];

    //Relations
    public function composites()
    {
        return $this->belongsToMany('Kommercio\Models\Product\Composite\ProductComposite')->withPivot('sort_order')->orderBy('sort_order', 'ASC');
    }

    public function products()
    {
        return $this->belongsToMany('Kommercio\Models\Product')->withPivot('sort_order');
    }

    //Accessors
    public function getCompositeCountAttribute()
    {
        return $this->composites->count();
    }
}
