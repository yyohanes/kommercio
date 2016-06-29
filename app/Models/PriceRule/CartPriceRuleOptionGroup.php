<?php

namespace Kommercio\Models\PriceRule;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Product;

class CartPriceRuleOptionGroup extends Model
{
    const TYPE_SHIPPING = 'shipping';
    const TYPE_PRODUCTS = 'products';

    public $optionFields = ['categories', 'manufacturers', 'attributeValues', 'featureValues'];
    public $timestamps = FALSE;

    protected $guarded = [];

    //Relations
    public function priceRule()
    {
        return $this->belongsTo('Kommercio\Models\PriceRule\CartPriceRule', 'cart_price_rule_id');
    }

    public function shippingMethods()
    {
        return $this->morphedByMany('Kommercio\Models\ShippingMethod\ShippingMethod', 'cart_price_rule_optionable');
    }

    public function manufacturers()
    {
        return $this->morphedByMany('Kommercio\Models\Manufacturer', 'cart_price_rule_optionable');
    }

    public function categories()
    {
        return $this->morphedByMany('Kommercio\Models\ProductCategory', 'cart_price_rule_optionable');
    }

    public function attributeValues()
    {
        return $this->morphedByMany('Kommercio\Models\ProductAttribute\ProductAttributeValue', 'cart_price_rule_optionable');
    }

    public function featureValues()
    {
        return $this->morphedByMany('Kommercio\Models\ProductFeature\ProductFeatureValue', 'cart_price_rule_optionable');
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

    public function getProducts()
    {
        $qb = Product::productSelection();

        $firstValidation = true;

        //Validate one because it's an OR validation
        foreach($this->optionFields as $optionField){
            switch($optionField){
                case 'categories':
                    if($this->$optionField->count() > 0){
                        $validationFunction = $firstValidation?'whereHas':'orWhereHas';
                        $qb->$validationFunction($optionField, function($qb) use ($optionField){
                            $qb->whereIn('id', $this->$optionField->pluck('id')->all());
                        });

                        $firstValidation = false;
                    }
                    break;
                case 'manufacturers':
                    if($this->$optionField->count() > 0){
                        $validationFunction = $firstValidation?'whereIn':'orWhereIn';
                        $qb->$validationFunction('manufacturer_id', $this->$optionField->pluck('id')->all());

                        $firstValidation = false;
                    }
                    break;
                case 'attributeValues':
                    if($this->$optionField->count() > 0){
                        $validationFunction = $firstValidation?'whereHas':'orWhereHas';
                        $qb->$validationFunction('productAttributeValues', function($qb) use ($optionField){
                            $qb->whereIn('id', $this->$optionField->pluck('id')->all());
                        });

                        $firstValidation = false;
                    }
                    break;
                case 'featureValues':
                    if($this->$optionField->count() > 0){
                        $validationFunction = $firstValidation?'whereHas':'orWhereHas';
                        $qb->$validationFunction('productFeatureValues', function($qb) use ($optionField){
                            $qb->whereIn('id', $this->$optionField->pluck('id')->all());
                        });

                        $firstValidation = false;
                    }
                    break;
                default:
                    break;
            }
        }

        $products = $qb->get();

        $return = [];
        foreach($products as $product){
            $return[$product->id] = $product;
        }

        return $return;
    }
}
