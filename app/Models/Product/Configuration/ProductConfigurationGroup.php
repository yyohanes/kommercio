<?php

namespace Kommercio\Models\Product\Configuration;

use Dimsav\Translatable\Translatable;
use Kommercio\Models\Abstracts\SluggableModel;

class ProductConfigurationGroup extends SluggableModel
{
    use Translatable;

    protected $fillable = ['name', 'slug'];

    public $translatedAttributes = ['name'];

    //Relations
    public function configurations()
    {
        return $this->belongsToMany('Kommercio\Models\Product\Configuration\ProductConfiguration')->withPivot(['label', 'sort_order', 'required'])->orderBy('sort_order', 'ASC');
    }

    public function products()
    {
        return $this->belongsToMany('Kommercio\Models\Product', 'product_configuration_group_product')->withPivot('sort_order');
    }

    //Accessors
    public function getConfigurationCountAttribute()
    {
        return $this->configurations->count();
    }
}
