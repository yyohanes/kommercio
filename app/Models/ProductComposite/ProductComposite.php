<?php

namespace Kommercio\Models\ProductComposite;

use Cviebrock\EloquentSluggable\SluggableInterface;
use Cviebrock\EloquentSluggable\SluggableTrait;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Product;

class ProductComposite extends Model implements SluggableInterface
{
    use Translatable, SluggableTrait;

    public $translatedAttributes = ['label'];

    protected $fillable = ['name', 'slug', 'label'];
    protected $sluggable = [
        'build_from' => 'name',
        'save_to'    => 'slug',
        'on_update' => TRUE,
    ];

    //Methods
    public function newPivot(Model $parent, array $attributes, $table, $exists)
    {
        if ($parent instanceof Product) {
            return new ProductCompositeConfigurationPivot($parent, $attributes, $table, $exists);
        }

        return parent::newPivot($parent, $attributes, $table, $exists);
    }

    //Relations
    public function products()
    {
        return $this->belongsToMany('Kommercio\Models\Product', 'product_composite_configurations')->withPivot(['minimum', 'maximum', 'sort_order'])->withTimestamps();
    }
}
