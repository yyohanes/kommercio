<?php

namespace Kommercio\Validator;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;
use Kommercio\Models\Address\Address;
use Kommercio\Models\Order\Order;
use Kommercio\Models\PriceRule\CartPriceRule;
use Kommercio\Models\Product;
use Symfony\Component\Translation\TranslatorInterface;

class CustomValidator extends Validator
{
    private static $_storage;

    public function __construct(TranslatorInterface $translator, array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        parent::__construct( $translator, $data, $rules, $messages, $customAttributes );

        $this->implicitRules[] = studly_case('descendant_address');
    }

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

    public function validateDescendantAddress($attribute, $value, $parameters)
    {
        $type = $parameters[0];

        $prefix = str_replace($type.'_id', '', $attribute);

        $parent = Address::getClassInfoByType($type)[1];

        $parentId = $this->getValue($prefix.$parent.'_id');

        $model = call_user_func(array(Address::getClassNameByType($parent), 'find'), $parentId);

        if($model && $model->has_descendant && $model->getChildren()->count() > 0){
            return !empty($value);
        }

        return true;
    }

    public function validateValidCoupon($attribute, $value, $parameters)
    {
        $order = Order::findOrFail($parameters[0]);

        $couponCode = empty($value)?'ERRORCODE':$value;

        static::$_storage[$couponCode] = CartPriceRule::getCoupon($couponCode, $order);

        //If above method returns string, it is returning error message
        return !is_string(static::$_storage[$couponCode]);
    }

    public function replaceValidCoupon($message, $attribute, $rule, $parameters)
    {
        $couponCode = $this->getValue($attribute);
        $message = is_string(static::$_storage[$couponCode])?static::$_storage[$couponCode]:'';

        return $message;
    }

    public function validateOldPassword($attribute, $value, $parameters)
    {
        return Hash::check($value, current($parameters));
    }

    protected function replaceProductAttribute($message, $attribute, $rule, $parameters)
    {
        $product_id = $this->getValue($attribute);
        $product = Product::findOrFail($product_id);

        return str_replace(':product', $product->name, $message);
    }
}