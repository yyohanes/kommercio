<?php

namespace Kommercio\Models;

use Illuminate\Database\Eloquent\Model;

class PriceRuleOptionGroup extends Model
{
    public $optionFields = ['categories', 'manufacturers', 'attributeValues', 'featureValues'];
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

    //Methods
    public function validateProduct(Product $product)
    {
        //Validate one because it's an OR validation
        foreach($this->optionFields as $optionField){
            switch($optionField){
                case 'categories':
                    $productValues = $product->categories->pluck('id')->all();
                    break;
                case 'manufacturers':
                    $productValues = $product->manufacturer_id?[$product->manufacturer_id]:null;
                    break;
                case 'attributeValues':
                    $productValues = $product->productAttributeValues->pluck('id')->all();
                    break;
                case 'featureValues':
                    $productValues = $product->productFeatureValues->pluck('id')->all();
                    break;
                default:
                    break;
            }

            if($productValues){
                $intersected = array_intersect($this->$optionField->pluck('id')->all(), $productValues);

                if(!empty($intersected)){
                    return TRUE;
                }
            }
        }

        return FALSE;
    }
}
