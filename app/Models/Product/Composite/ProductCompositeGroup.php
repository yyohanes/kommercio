<?php

namespace Kommercio\Models\Product\Composite;

use Kommercio\Models\Abstracts\SluggableModel;

class ProductCompositeGroup extends SluggableModel
{
    protected $fillable = ['name', 'slug'];

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
