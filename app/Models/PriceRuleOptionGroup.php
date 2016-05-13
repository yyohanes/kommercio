<?php

namespace Kommercio\Models;

use Illuminate\Database\Eloquent\Model;

class PriceRuleOptionGroup extends Model
{
    public $optionFields = ['manufacturers', 'categories', 'attributeValues', 'featureValues'];
    public $timestamps = FALSE;

    protected $guarded = [];

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
