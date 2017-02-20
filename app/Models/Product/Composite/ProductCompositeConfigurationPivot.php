<?php

namespace Kommercio\Models\Product\Composite;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductCompositeConfigurationPivot extends Pivot
{
    protected $table = 'product_product_composite_configuration';
    protected $fillable = ['maximum', 'minimum', 'sort_order'];

    private $_isSingle;

    //Accessors
    public function getIsSingleAttribute()
    {
        if(!isset($this->_isSingle)){
            if($this->configuredProducts->count() == 1 && $this->configuredProducts->get(0)->isPurchaseable && $this->minimum > 0 && $this->minimum == $this->maximum){
                $this->_isSingle = true;
            }
        }

        return $this->_isSingle;
    }

    public function getConfiguredProductAttribute()
    {
        if($this->_isSingle){
            return $this->configuredProducts->get(0);
        }
    }

    //Relations
    public function configuredProducts()
    {
        return $this->belongsToMany('Kommercio\Models\Product', 'product_product_composite_configuration', 'product_composite_configuration_id', 'product_id')->withPivot(['sort_order'])->orderBy('sort_order', 'ASC');
    }
}