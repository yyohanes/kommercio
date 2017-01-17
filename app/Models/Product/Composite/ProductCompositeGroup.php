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
        'on_update' => TRUE
    ];

    //Methods
    public function getViewSuggestions()
    {
        $viewSuggestions = ['frontend.catalog.product_composite.view_'.$this->id, 'frontend.catalog.product_composite.view'];

        return $viewSuggestions;
    }

    //Relations
    public function composites()
    {
        return $this->belongsToMany('Kommercio\Models\Product\Composite\ProductComposite')->withPivot('sort_order')->orderBy('sort_order', 'ASC');
    }

    public function products()
    {
        return $this->belongsToMany('Kommercio\Models\Product', 'product_composite_group_product')->withPivot('sort_order');
    }

    //Accessors
    public function getCompositeCountAttribute()
    {
        return $this->composites->count();
    }
}
