<?php

namespace Kommercio\Validator;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;
use Kommercio\Models\Address\Address;
use Kommercio\Models\Order\Order;
use Kommercio\Models\PaymentMethod\PaymentMethod;
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

    public function validateDeliveryOrderLimit($attribute, $value, $parameters)
    {
        return $this->processValidateOrderLimit('delivery_date', $attribute, $value, $parameters);
    }

    public function replaceDeliveryOrderLimit($message, $attribute, $rule, $parameters)
    {
        $delivery_date = $parameters[2];
        $delivery_date = Carbon::createFromFormat('Y-m-d', $delivery_date)->format('d F Y');

        $left = static::$_storage['delivery_date_'.$this->getValue($attribute).'_available_quantity']?:0;
        $left = $left < 1?0:$left;

        $message = $this->replaceProductAttribute($message, $attribute, $rule, $parameters);
        $message = str_replace(':date', $delivery_date, $message);
        $message = str_replace(':quantity', $left, $message);

        return $message;
    }

    public function validateTodayOrderLimit($attribute, $value, $parameters)
    {
        return $this->processValidateOrderLimit('checkout_at', $attribute, $value, $parameters);
    }

    public function replaceTodayOrderLimit($message, $attribute, $rule, $parameters)
    {
        $left = static::$_storage['checkout_at_'.$this->getValue($attribute).'_available_quantity']?:0;
        $left = $left < 1?0:$left;

        $message = $this->replaceProductAttribute($message, $attribute, $rule, $parameters);
        $message = str_replace(':quantity', $left, $message);

        return $message;
    }

    public function validatePerOrderLimit($attribute, $value, $parameters)
    {
        $product_id = $value;
        $quantity = $parameters[0];
        $order_id = $parameters[1];

        $product = Product::findOrFail($product_id);
        $order = Order::findOrFail($order_id);

        $orderLimit = $product->getPerOrderLimit([
            'store' => $order->store_id,
            'date' => Carbon::now()->format('Y-m-d')
        ]);

        if($orderLimit){
            static::$_storage['per_order_'.$product->id.'_available_quantity'] = $orderLimit + 0;

            return $orderLimit >= $quantity;
        }

        return true;
    }

    public function replacePerOrderLimit($message, $attribute, $rule, $parameters)
    {
        $message = $this->replaceProductAttribute($message, $attribute, $rule, $parameters);
        $message = str_replace(':quantity', static::$_storage['per_order_'.$this->getValue($attribute).'_available_quantity'], $message);

        return $message;
    }

    public function validateCompositeQuantity($attribute, $value, $parameters)
    {
        $sku = $value;

        if(!$sku){
            return true;
        }

        $product = Product::where('sku', $parameters[0])->firstOrFail();

        $composite = $parameters[1];
        $composite = $product->getCompositeConfiguration((int) $composite);

        static::$_storage['composite_'.$product->sku.'_'.$composite->id] = $composite;

        $quantity = floatval($parameters[2]);

        return $composite->pivot->minimum <= $quantity && $composite->pivot->maximum >= $quantity;
    }

    public function replaceCompositeQuantity($message, $attribute, $rule, $parameters)
    {
        $product = Product::where('sku', $parameters[0])->firstOrFail();

        $message = str_replace(':product', $product->name, $message);
        $message = str_replace(':composite', static::$_storage['composite_'.$parameters[0].'_'.$parameters[1]]->name, $message);

        if(static::$_storage['composite_'.$parameters[0].'_'.$parameters[1]]->pivot->minimum == static::$_storage['composite_'.$parameters[0].'_'.$parameters[1]]->pivot->maximum){
            $quantity = static::$_storage['composite_'.$parameters[0].'_'.$parameters[1]]->pivot->minimum + 0;
        }else{
            $quantity = static::$_storage['composite_'.$parameters[0].'_'.$parameters[1]]->pivot->minimum+0 .' - '.static::$_storage['composite_'.$parameters[0].'_'.$parameters[1]]->pivot->maximum+0;
        }

        $message = str_replace(':quantity', $quantity, $message);

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

    protected function validateStepPaymentMethod($attribute, $value, $parameters)
    {
        $paymentMethod = PaymentMethod::findOrFail($value);

        $order = Order::findOrFail($parameters[0]);

        return $paymentMethod->getProcessor()->stepPaymentMethodValidation([
            'order' => $order
        ]);
    }

    protected function validatePaymentMethod($attribute, $value, $parameters)
    {
        $paymentMethod = PaymentMethod::findOrFail($value);

        $order = Order::findOrFail($parameters[0]);

        return $paymentMethod->getProcessor()->paymentMethodValidation([
            'order' => $order
        ]);
    }

    protected function processValidateOrderLimit($type, $attribute, $value, $parameters)
    {
        $product_id = $value;
        $quantity = $parameters[0];
        $order_id = $parameters[1];

        $delivery_date = null;
        if($type == 'delivery_date' && isset($parameters[2])){
            $delivery_date = $parameters[2];
        }

        $today = null;
        if($type == 'checkout_at'){
            $today = Carbon::now()->format('Y-m-d');
        }

        $order = Order::findOrFail($order_id);
        $product = Product::findOrFail($product_id);

        $orderCount = $product->getOrderCount([
            'delivery_date' => $delivery_date,
            'checkout_at' => $today,
            'store' => $order->store_id
        ]);

        $orderLimit = $product->getOrderLimit([
            'store' => $order->store_id,
            'date' => $today,
            'delivery_date' => $delivery_date
        ]);

        if(is_array($orderLimit) && $orderLimit['limit_type'] == $type){
            static::$_storage[$type.'_'.$product->id.'_available_quantity'] = $orderLimit['limit'] - $orderCount;

            return ($orderLimit['limit'] - $orderCount) >= $quantity;
        }

        return true;
    }
}