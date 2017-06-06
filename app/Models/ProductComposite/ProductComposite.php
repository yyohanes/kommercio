<?php

namespace Kommercio\Models\ProductComposite;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Product;
use Kommercio\Models\Abstracts\SluggableModel;

class ProductComposite extends SluggableModel
{
    use Translatable;

    public $translatedAttributes = ['label'];

    protected $fillable = ['name', 'slug', 'label'];
    protected $sluggable = [
        'build_from' => 'name',
        'save_to'    => 'slug',
        'on_update' => TRUE,
    ];

    //Methods
    public function newPivot(Model $parent, array $attributes, $table, $exists, $using = null)
    {
        if ($parent instanceof Product) {
            return new ProductCompositeConfigurationPivot($parent, $attributes, $table, $exists, $using);
        }

        return parent::newPivot($parent, $attributes, $table, $exists, $using);
    }

    //Relations
    public function products()
    {
        return $this->belongsToMany('Kommercio\Models\Product', 'product_composite_configurations')->withPivot(['minimum', 'maximum', 'sort_order'])->withTimestamps();
    }
}
