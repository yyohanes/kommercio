<?php

namespace Kommercio\Models\Product\Configuration;

use Cviebrock\EloquentSluggable\SluggableInterface;
use Cviebrock\EloquentSluggable\SluggableTrait;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class ProductConfigurationGroup extends Model implements SluggableInterface
{
    use SluggableTrait, Translatable;

    protected $fillable = ['name', 'slug'];
    protected $sluggable = [
        'build_from' => 'name',
        'save_to' => 'slug',
        'on_update' => TRUE
    ];

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
