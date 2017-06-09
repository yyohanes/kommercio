<?php

namespace Kommercio\Validator;

use Carbon\Carbon;
use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;
use Kommercio\Facades\RuntimeCache;
use Kommercio\Models\Address\Address;
use Kommercio\Models\Customer;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Order\OrderLimit;
use Kommercio\Models\PaymentMethod\PaymentMethod;
use Kommercio\Models\PriceRule\CartPriceRule;
use Kommercio\Models\Product;
use Kommercio\Models\ProductCategory;
use Kommercio\Models\RewardPoint\Reward;

class CustomValidator extends Validator
{
    private static $_storage;

    public function __construct(Translator $translator, array $data, array $rules, array $messages = [], array $customAttributes = [])
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
            $variations = $variations->reject(function($value) use ($variation, $attributes){
                return $value->id == $variation || $value->productAttributes->count() != count($attributes);
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

        $product = RuntimeCache::getOrSet('product_'.$product_id, function() use ($product_id){
            return Product::findOrFail($product_id);
        });

        return $product->productDetail->active;
    }

    public function replaceIsActive($message, $attribute, $rule, $parameters)
    {
        return $this->replaceProductAttribute($message, $attribute, $rule, $parameters);
    }

    public function validateIsAvailable($attribute, $value, $parameters)
    {
        $product_id = $value;

        $product = RuntimeCache::getOrSet('product_'.$product_id, function() use ($product_id){
            return Product::findOrFail($product_id);
        });

        return $product->productDetail->available;
    }

    public function replaceIsAvailable($message, $attribute, $rule, $parameters)
    {
        return $this->replaceProductAttribute($message, $attribute, $rule, $parameters);
    }

    public function validateIsPurchaseable($attribute, $value, $parameters)
    {
        $product_id = $value;

        $product = RuntimeCache::getOrSet('product_'.$product_id, function() use ($product_id){
            return Product::findOrFail($product_id);
        });

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

        $product = RuntimeCache::getOrSet('product_'.$product_id, function() use ($product_id){
            return Product::findOrFail($product_id);
        });

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

        //Call getCoupon from CartPriceRule because it has validation function
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

        $message = $this->replaceProductAttribute($message, $attribute, $rule, $parameters);
        $message = str_replace(':date', $delivery_date, $message);

        if(static::$_storage['order_limit']->type == OrderLimit::TYPE_PRODUCT_CATEGORY){
            $message = str_replace(':quantity', static::$_storage['order_limit']->limit + 0, $message);
        }else{
            $left = static::$_storage['delivery_date_'.$this->getValue($attribute).'_available_quantity']?:0;
            $left = $left < 1?0:$left;

            $message = str_replace(':quantity', $left, $message);
        }

        return $message;
    }

    public function validateTodayOrderLimit($attribute, $value, $parameters)
    {
        return $this->processValidateOrderLimit('checkout_at', $attribute, $value, $parameters);
    }

    public function replaceTodayOrderLimit($message, $attribute, $rule, $parameters)
    {
        $message = $this->replaceProductAttribute($message, $attribute, $rule, $parameters);

        $left = static::$_storage['checkout_at_'.$this->getValue($attribute).'_available_quantity']?:0;
        $left = $left < 1?0:$left;

        $message = str_replace(':quantity', $left, $message);

        return $message;
    }

    public function validatePerOrderLimit($attribute, $value, $parameters)
    {
        $product_id = $value;
        $quantity = $parameters[0];
        $order_id = $parameters[1];

        if($quantity > 0){
            $product = RuntimeCache::getOrSet('product_'.$product_id, function() use ($product_id){
                return Product::findOrFail($product_id);
            });
            $order = RuntimeCache::getOrSet('order_'.$order_id, function() use ($order_id){
                return Order::findOrFail($order_id);
            });

            $orderLimit = $product->getPerOrderLimit([
                'store' => !empty($order->store)?$order->store->id:null,
                'date' => Carbon::now()->format('Y-m-d')
            ]);

            $productPassed = true;

            if($orderLimit){
                static::$_storage['per_order_'.$product->id.'_available_quantity'] = $orderLimit['limit'] + 0;
                static::$_storage['order_limit'] = $orderLimit['object'];
                static::$_storage['invalidated_object'] = $product;

                $productPassed = $orderLimit['limit'] >= $quantity;
            }

            if($productPassed){
                foreach($product->categories as $category){
                    $orderLimit = $category->getPerOrderLimit([
                        'store' => !empty($order->store)?$order->store->id:null,
                        'date' => Carbon::now()->format('Y-m-d')
                    ]);

                    if($orderLimit){
                        $currentOrderedTotal = RuntimeCache::getOrSet('per_order_category_total_'.$category->id, function() use ($quantity){
                            return 0;
                        });

                        $currentOrderedTotal += $quantity;

                        RuntimeCache::set('per_order_category_total_'.$category->id, $currentOrderedTotal);

                        $productCategoryPassed = $orderLimit['limit'] >= $currentOrderedTotal;

                        if(!$productCategoryPassed){
                            static::$_storage['order_limit'] = $orderLimit['object'];
                            static::$_storage['invalidated_object'] = $category;
                            return $productCategoryPassed;
                        }
                    }
                }
            }
        }

        return true;
    }

    public function replacePerOrderLimit($message, $attribute, $rule, $parameters)
    {
        $message = $this->replaceProductAttribute($message, $attribute, $rule, $parameters);

        if(static::$_storage['order_limit']->type == OrderLimit::TYPE_PRODUCT){
            $message = str_replace(':quantity', static::$_storage['per_order_'.$this->getValue($attribute).'_available_quantity'], $message);
        }else{
            $message = str_replace(':quantity', static::$_storage['order_limit']->limit + 0, $message);
        }

        return $message;
    }

    public function validateOldPassword($attribute, $value, $parameters)
    {
        return Hash::check($value, current($parameters));
    }

    public function validateRedemption($attribute, $value, $parameters)
    {
        $customer = Customer::findOrFail($parameters[0]);
        $reward = RuntimeCache::getOrSet('reward_'.$value, function() use ($value){
            return Reward::findOrFail($value);
        });

        return $customer->reward_points >= $reward->points;
    }

    public function replaceRedemption($message, $attribute, $rule, $parameters)
    {
        $reward = RuntimeCache::get('reward_'.$this->getValue($attribute));

        $message = str_replace(':reward', $reward->name, $message);

        return $message;
    }

    protected function replaceProductAttribute($message, $attribute, $rule, $parameters)
    {
        if(!isset(static::$_storage['invalidated_object'])){
            $product_id = $this->getValue($attribute);

            static::$_storage['invalidated_object'] = $product = RuntimeCache::getOrSet('product_'.$product_id, function() use ($product_id){
                return Product::findOrFail($product_id);
            });
        }

        $invalidatedObject = static::$_storage['invalidated_object'];

        return str_replace(':product', $invalidatedObject->name, $message);
    }

    protected function validateStepPaymentMethod($attribute, $value, $parameters)
    {
        $paymentMethod = PaymentMethod::findOrFail($value);

        $order_id = $parameters[0];
        $order = RuntimeCache::getOrSet('order_'.$order_id, function() use ($order_id){
            return Order::findOrFail($order_id);
        });

        return $paymentMethod->getProcessor()->stepPaymentMethodValidation([
            'order' => $order
        ]);
    }

    protected function validatePaymentMethod($attribute, $value, $parameters)
    {
        $paymentMethod = PaymentMethod::findOrFail($value);

        $order_id = $parameters[0];

        $order = RuntimeCache::getOrSet('order_'.$order_id, function() use ($order_id){
            return Order::findOrFail($order_id);
        });

        return $paymentMethod->getProcessor()->paymentMethodValidation([
            'order' => $order
        ]);
    }

    protected function processValidateOrderLimit($type, $attribute, $value, $parameters)
    {
        $product_id = $value;
        $quantity = $parameters[0];
        $order_id = $parameters[1];

        if($type == 'delivery_date'){
            $store_id = isset($parameters[3])?$parameters[3]:null;
        }else{
            $store_id = isset($parameters[2])?$parameters[2]:null;
        }

        if($quantity > 0){
            $delivery_date = null;
            if($type == 'delivery_date' && isset($parameters[2])){
                $delivery_date = $parameters[2];
            }

            $today = null;
            if($type == 'checkout_at'){
                $today = Carbon::now()->format('Y-m-d');
            }

            $product = RuntimeCache::getOrSet('product_'.$product_id, function() use ($product_id){
                return Product::findOrFail($product_id);
            });
            $order = RuntimeCache::getOrSet('order_'.$order_id, function() use ($order_id){
                return Order::find($order_id);
            });

            $orderCount = $product->getOrderCount([
                'delivery_date' => $delivery_date,
                'checkout_at' => $today,
                'store_id' => $store_id?:(!empty($order->store)?$order->store->id:null),
            ]);

            $orderLimit = $product->getOrderLimit([
                'store' => $store_id?:(!empty($order->store)?$order->store->id:null),
                'date' => $today,
                'delivery_date' => $delivery_date,
                'type' => OrderLimit::TYPE_PRODUCT
            ]);

            $productLimitPassed = true;

            if(is_array($orderLimit) && $orderLimit['limit_type'] == $type){
                static::$_storage[$type.'_'.$product->id.'_available_quantity'] = $orderLimit['limit'] - $orderCount;

                $productLimitPassed = ($orderLimit['limit'] - $orderCount) >= $quantity;

                if(!$productLimitPassed){
                    static::$_storage['order_limit'] = $orderLimit['object'];
                    static::$_storage['invalidated_object'] = $product;

                    return $productLimitPassed;
                }
            }

            // Check Category Limit if product limit passes
            if($productLimitPassed){
                $categoryOrderLimit = $product->getOrderLimit([
                    'store' => $store_id?:(!empty($order->store)?$order->store->id:null),
                    'date' => $today,
                    'delivery_date' => $delivery_date,
                    'type' => OrderLimit::TYPE_PRODUCT_CATEGORY,
                ]);

                if(is_array($categoryOrderLimit)){
                    foreach($categoryOrderLimit['object']->productCategories as $productCategory){
                        $categoryOrderCount = $productCategory->getOrderCount([
                            'delivery_date' => $delivery_date,
                            'checkout_at' => $today,
                            'store_id' => $store_id?:(!empty($order->store)?$order->store->id:null),
                        ]);

                        $categoryLimitPassed = ($categoryOrderLimit['limit'] - $categoryOrderCount) >= $quantity;

                        if(!$categoryLimitPassed){
                            static::$_storage[$type.'_'.$product->id.'_available_quantity'] = $categoryOrderLimit['limit'] - $categoryOrderCount;
                            static::$_storage['order_limit'] = $categoryOrderLimit['object'];
                            static::$_storage['invalidated_object'] = $productCategory;

                            return $categoryLimitPassed;
                        }
                    }
                }
            }
        }

        return true;
    }
}