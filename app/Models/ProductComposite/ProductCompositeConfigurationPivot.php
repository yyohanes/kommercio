<?php

namespace Kommercio\Models\ProductComposite;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductCompositeConfigurationPivot extends Pivot
{
    protected $table = 'product_product_composite_configuration';
    protected $fillable = ['maximum', 'minimum', 'sort_order'];

    //Relations
    public function configuredProducts()
    {
        return $this->belongsToMany('Kommercio\Models\Product', 'product_product_composite_configuration', 'product_composite_configuration_id', 'product_id')->withPivot(['sort_order'])->orderBy('sort_order', 'ASC');
    }
}