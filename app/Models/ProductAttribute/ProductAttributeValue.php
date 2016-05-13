<?php

namespace Kommercio\Models\ProductAttribute;

use Illuminate\Database\Eloquent\Model;
use Dimsav\Translatable\Translatable;
use Kommercio\Models\Product;

class ProductAttributeValue extends Model
{
    use Translatable;

    public $timestamps = FALSE;

    public $translatedAttributes = ['name', 'slug'];

    protected $guarded = [];

    //Methods
    public function newPivot(Model $parent, array $attributes, $table, $exists)
    {
        if ($parent instanceof Product) {
            return new AttributeValuePivot($parent, $attributes, $table, $exists);
        }

        return parent::newPivot($parent, $attributes, $table, $exists);
    }

    //Relations
    public function productAttribute()
    {
        return $this->belongsTo('Kommercio\Models\ProductAttribute\ProductAttribute');
    }

    public function products()
    {
        return $this->belongsToMany('Kommercio\Models\Product', 'product_product_attribute');
    }
}
