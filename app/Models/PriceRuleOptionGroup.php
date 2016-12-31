<?php

namespace Kommercio\Models;

use Illuminate\Database\Eloquent\Model;

class PriceRuleOptionGroup extends Model
{
    public $optionFields = ['categories', 'manufacturers', 'attributeValues', 'featureValues'];
    public $timestamps = FALSE;

    protected $guarded = [];

    //Methods
    public function getProducts()
    {
        $qb = Product::query();

        if($this->categories->count() > 0){
            $qb->whereHas('categories', function($query){
                $query->whereIn('id', $this->categories->pluck('id')->all());
            });
        }

        if($this->manufacturers->count() > 0){
            $qb->whereHas('manufacturer', function($query){
                $query->whereIn('id', $this->manufacturers->pluck('id')->all());
            });
        }

        if($this->featureValues->count() > 0){
            $qb->whereHas('productFeatureValues', function($query){
                $query->whereIn('id', $this->featureValues->pluck('id')->all());
            });
        }

        if($this->attributeValues->count() > 0){
            $qb->whereHas('productAttributeValues', function($query){
                $query->whereIn('id', $this->attributeValues->pluck('id')->all());
            });
        }

        return $qb->get();
    }

    //Relations
    public function priceRule()
    {
        return $this->belongsTo('Kommercio\Models\PriceRule');
    }

    public function manufacturers()
    {
        return $this->morphedByMany('Kommercio\Models\Manufacturer', 'price_rule_optionable');
    }

    public function categories()
    {
        return $this->morphedByMany('Kommercio\Models\ProductCategory', 'price_rule_optionable');
    }

    public function attributeValues()
    {
        return $this->morphedByMany('Kommercio\Models\ProductAttribute\ProductAttributeValue', 'price_rule_optionable');
    }

    public function featureValues()
    {
        return $this->morphedByMany('Kommercio\Models\ProductFeature\ProductFeatureValue', 'price_rule_optionable');
    }
}
