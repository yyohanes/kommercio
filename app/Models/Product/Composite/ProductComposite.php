<?php

namespace Kommercio\Models\Product\Composite;

use Cviebrock\EloquentSluggable\SluggableInterface;
use Cviebrock\EloquentSluggable\SluggableTrait;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Product;

class ProductComposite extends Model implements SluggableInterface
{
    use Translatable, SluggableTrait;

    public $translatedAttributes = ['label'];

    protected $fillable = ['name', 'slug', 'label', 'minimum', 'maximum', 'sort_order'];
    protected $sluggable = [
        'build_from' => 'name',
        'save_to'    => 'slug',
        'on_update' => TRUE,
    ];

    //Relations
    public function groups()
    {
        return $this->belongsToMany('Kommercio\Models\Product\Composite\ProductCompositeGroup');
    }

    public function products()
    {
        return $this->belongsToMany('Kommercio\Models\Product')->withPivot('sort_order')->orderBy('sort_order', 'ASC');
    }

    public function productCategories()
    {
        return $this->belongsToMany('Kommercio\Models\ProductCategory');
    }
}
