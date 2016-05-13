<?php

namespace Kommercio\Models\ProductFeature;

use Illuminate\Database\Eloquent\Model;
use Dimsav\Translatable\Translatable;
use Kommercio\Models\Product;

class ProductFeatureValue extends Model
{
    use Translatable;

    public $timestamps = FALSE;

    protected $guarded = [];
    protected $translatedAttributes = ['name', 'slug'];
    protected $casts = [
        'custom' => 'boolean'
    ];

    //Methods
    public function newPivot(Model $parent, array $attributes, $table, $exists)
    {
        if ($parent instanceof Product) {
            return new FeatureValuePivot($parent, $attributes, $table, $exists);
        }

        return parent::newPivot($parent, $attributes, $table, $exists);
    }

    //Relations
    public function productFeature()
    {
        return $this->belongsTo('Kommercio\Models\ProductFeature\ProductFeature');
    }

    public function products()
    {
        return $this->belongsToMany('Kommercio\Models\Product', 'product_product_feature');
    }
}
