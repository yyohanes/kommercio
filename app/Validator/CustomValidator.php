<?php

namespace Kommercio\Validator;

use Illuminate\Validation\Validator;
use Kommercio\Models\Product;
use Symfony\Component\Translation\TranslatorInterface;

class CustomValidator extends Validator
{
    public function validateProductAttributes($attribute, $value, $parameters)
    {
        $data = $this->getValue($attribute);

        $attributes = array_keys($data);
        $attributeValues = $data;

        $product = Product::findOrFail($parameters[0]);
        $variation = isset($parameters[1])?$parameters[1]:null;

        $variations = $product->getVariationsByAttributes($attributes, $attributeValues);

        if($variation){
            $variations = $variations->reject(function($value) use ($variation){
                return $value->id == $variation;
            });
        }

        return $variations->count() < 1;
    }

    public function validateProductSKU($attribute, $value, $parameters)
    {
        return Product::where('sku', $value)->count() > 0;
    }

    public function validateIsActive($attribute, $value, $parameters)
    {
        $product_id = $value;

        $product = Product::findOrFail($product_id);

        return $product->productDetail->active;
    }

    public function replaceIsActive($message, $attribute, $rule, $parameters)
    {
        return $this->replaceProductAttribute($message, $attribute, $rule, $parameters);
    }

    public function validateIsAvailable($attribute, $value, $parameters)
    {
        $product_id = $value;

        $product = Product::findOrFail($product_id);

        return $product->productDetail->available;
    }

    public function replaceIsAvailable($message, $attribute, $rule, $parameters)
    {
        return $this->replaceProductAttribute($message, $attribute, $rule, $parameters);
    }

    public function validateIsPurchaseable($attribute, $value, $parameters)
    {
        $product_id = $value;

        $product = Product::findOrFail($product_id);

        return $product->isPurchaseable;
    }

    public function replaceIsPurchaseable($message, $attribute, $rule, $parameters)
    {
        return $this->replaceProductAttribute($message, $attribute, $rule, $parameters);
    }

    public function validateIsInStock($attribute, $value, $parameters)
    {
        $product_id = $value;
        $amount = $parameters[0];

        $product = Product::findOrFail($product_id);

        return $product->checkStock($amount);
    }

    public function replaceIsInStock($message, $attribute, $rule, $parameters)
    {
        return $this->replaceProductAttribute($message, $attribute, $rule, $parameters);
    }

    protected function replaceProductAttribute($message, $attribute, $rule, $parameters)
    {
        $product_id = $this->getValue($attribute);
        $product = Product::findOrFail($product_id);

        return str_replace(':product', $product->name, $message);
    }
}