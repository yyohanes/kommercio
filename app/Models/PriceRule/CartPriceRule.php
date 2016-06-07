<?php

namespace Kommercio\Models\PriceRule;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Facades\PriceFormatter;
use Kommercio\Models\Customer;
use Kommercio\Models\Order\Order;
use Kommercio\Traits\Model\ToggleDate;

class CartPriceRule extends Model
{
    use ToggleDate;

    const MODIFICATION_TYPE_PERCENT = 'percent';
    const MODIFICATION_TYPE_AMOUNT = 'amount';

    const OFFER_TYPE_FREE_SHIPPING = 'free_shipping';
    const OFFER_TYPE_ORDER_DISCOUNT = 'order_discount';
    const OFFER_TYPE_PRODUCT_DISCOUNT = 'product_discount';

    protected $fillable = ['name', 'price', 'modification', 'modification_type',
        'currency', 'store_id', 'customer_id', 'minimum_subtotal', 'max_usage', 'max_usage_per_customer', 'offer_type', 'active', 'active_date_from', 'active_date_to', 'sort_order'];
    protected $toggleFields = ['active'];
    protected $casts = [
        'active' => 'boolean',
    ];

    //Relations
    public function store()
    {
        return $this->belongsTo('Kommercio\Models\Store');
    }

    public function customer()
    {
        return $this->belongsTo('Kommercio\Models\Customer');
    }

    public function shippingOptionGroup()
    {
        return $this->hasOne('Kommercio\Models\PriceRule\CartPriceRuleOptionGroup')->where('type', CartPriceRuleOptionGroup::TYPE_SHIPPING);
    }

    public function productOptionGroups()
    {
        return $this->hasMany('Kommercio\Models\PriceRule\CartPriceRuleOptionGroup')->where('type', CartPriceRuleOptionGroup::TYPE_PRODUCTS);
    }

    //Scopes
    public function scopeActive($query)
    {
        $query->where('active', 1);
    }

    //Methods
    public function getValue($price = null)
    {
        if(!is_null($this->price)){
            return $this->price;
        }else{
            if(is_null($price)){
                $price = $this->price;
            }

            if($this->modification_type == 'amount'){
                return $price + $this->modification;
            }else{
                return $price + ($price * $this->modification / 100);
            }
        }
    }

    public function getModificationOutput()
    {
        if($this->modification_type == 'amount'){
            return PriceFormatter::formatNumber($this->modification, $this->currency);
        }else{
            return ($this->modification+0).'%';
        }
    }

    public function getUsage()
    {
        $qb = Order::whereHasLineItem($this->id, 'cart_price_rule')->usageCounted();

        return $qb->count();
    }

    public function getUsageByEmail($email)
    {
        $customer = Customer::getByEmail($email);

        if(!$customer){
            return 0;
        }

        $qb = Order::whereHasLineItem($this->id, 'cart_price_rule')->usageCounted()->where('customer_id', $customer->id);

        return $qb->count();
    }

    public function validateUsage($email = null)
    {
        $valid = is_null($this->max_usage) || $this->max_usage > $this->getUsage();

        if($valid && $email){
            $valid = is_null($this->max_usage_per_customer) || $this->max_usage_per_customer > $this->getUsageByEmail($email);
        }

        return $valid;
    }

    //Statics
    public static function getModificationTypeOptions($option=null)
    {
        $array = [
            self::MODIFICATION_TYPE_AMOUNT => 'Amount',
            self::MODIFICATION_TYPE_PERCENT => 'Percent',
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }

    public static function getOfferTypeOptions($option=null)
    {
        $array = [
            //self::OFFER_TYPE_FREE_SHIPPING => 'Free Shipping',
            self::OFFER_TYPE_ORDER_DISCOUNT => 'Order Discount',
            self::OFFER_TYPE_PRODUCT_DISCOUNT => 'Product Discount',
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }

    public static function getCartPriceRules($options)
    {
        $qb = self::orderBy('sort_order', 'ASC')->active();

        $subtotal = isset($options['subtotal'])?$options['subtotal']:null;
        $customer = null;
        $customer_email = null;
        if(isset($options['customer_email'])){
            $customer = Customer::getByEmail($options['customer_email']);
            $customer_email = $options['customer_email'];
        }

        $currency = isset($options['currency'])?:null;
        $store = isset($options['store_id'])?:null;

        $shippings = isset($options['shippings'])?$options['shippings']:null;

        if($currency){
            $qb->where(function($qb) use ($currency){
                $qb->whereNull('currency')->orWhere('currency', $currency);
            });
        }

        if($store){
            $qb->where(function($qb) use ($store){
                $qb->whereNull('store_id')->orWhere('store_id', $store);
            });
        }

        if($customer){
            $qb->where(function($qb) use ($customer){
                $qb->whereNull('customer_id')->orWhere('customer_id', $customer->id);
            });
        }

        if(!is_null($subtotal)){
            $qb->where(function($qb) use ($subtotal){
                $qb->whereNull('minimum_subtotal')->orWhere('minimum_subtotal', '<=', $subtotal);
            });
        }

        if($shippings){
            $qb->where(function($qb) use ($shippings){
                $qb->whereDoesntHave('shippingOptionGroup.shippingMethods')->orWhereHas('shippingOptionGroup.shippingMethods', function ($query) use ($shippings) {
                    $query->whereIn('id', $shippings);
                });
            });
        }

        $priceRules = $qb->get();

        foreach($priceRules as $idx=>$priceRule){
            if(!$priceRule->validateUsage($customer_email)){
                unset($priceRules[$idx]);
            }
        }

        return $priceRules;
    }
}
