<?php

namespace Kommercio\Models\PriceRule;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Facades\PriceFormatter;
use Kommercio\Models\Customer;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Product;
use Kommercio\Traits\Model\ToggleDate;

class CartPriceRule extends Model
{
    use ToggleDate;

    const MODIFICATION_TYPE_PERCENT = 'percent';
    const MODIFICATION_TYPE_AMOUNT = 'amount';

    const OFFER_TYPE_FREE_SHIPPING = 'free_shipping';
    const OFFER_TYPE_ORDER_DISCOUNT = 'order_discount';
    const OFFER_TYPE_PRODUCT_DISCOUNT = 'product_discount';

    protected $fillable = ['name', 'coupon_code', 'price', 'modification', 'modification_type',
        'currency', 'store_id', 'customer_id', 'minimum_subtotal', 'max_usage', 'max_usage_per_customer', 'offer_type', 'active', 'active_date_from', 'active_date_to', 'sort_order'];
    protected $toggleFields = ['active'];
    protected $casts = [
        'active' => 'boolean',
    ];

    //To store calculated total
    public $total = 0;

    //Relations
    public function store()
    {
        return $this->belongsTo('Kommercio\Models\Store');
    }

    public function customer()
    {
        return $this->belongsTo('Kommercio\Models\Customer');
    }

    public function products()
    {
        return $this->belongsToMany('Kommercio\Models\Product');
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
        $calculatedAmount = $price;

        if(!is_null($this->price)){
            $calculatedAmount = $this->price;
        }

        if($this->modification_type == self::MODIFICATION_TYPE_AMOUNT && !is_null($this->modification)){
            $calculatedAmount += $this->modification;
        }elseif($this->modification_type == self::MODIFICATION_TYPE_PERCENT && !is_null($this->modification)){
            $calculatedAmount += ($calculatedAmount * $this->modification/100);
        }

        return PriceFormatter::round($calculatedAmount);
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
        $message = 'order.coupons.successfully_added';

        if(!$valid){
            $message = 'order.coupons.max_usage_exceeded';
        }

        if($valid && $email){
            $valid = is_null($this->max_usage_per_customer) || $this->max_usage_per_customer > $this->getUsageByEmail($email);
            if(!$valid){
                $message = 'order.coupons.max_usage_per_email_exceeded';
            }
        }

        return [
            'valid' => $valid,
            'message' => $message
        ];
    }

    public function validateProduct(Product $product, $options = [])
    {
        $validateResults = [];

        foreach($this->productOptionGroups as $priceRuleOptionGroup){
            $validateResults[] = $priceRuleOptionGroup->validateProduct($product);
        }

        if(count(array_unique($validateResults)) === 1){
            return current($validateResults);
        }

        return TRUE;
    }

    public function getProducts()
    {
        if($this->products->count() > 0){
            return $this->products->all();
        }

        $optionGroupProducts = [];
        foreach($this->productOptionGroups as $idx => $productOptionGroup){
            if($idx == 0){
                $optionGroupProducts = $productOptionGroup->getProducts();
            }else{
                $optionGroupProducts = array_intersect_key($optionGroupProducts, $productOptionGroup->getProducts());
            }
        }

        return $optionGroupProducts;
    }

    //Accessors
    public function getIsCouponAttribute()
    {
        return !empty($this->coupon_code);
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
        if(!empty($options['customer_email'])){
            $customer = Customer::getByEmail($options['customer_email']);
        }

        $coupon_code = isset($options['coupon_code'])?$options['coupon_code']:null;

        $currency = isset($options['currency'])?$options['currency']:null;
        $store = isset($options['store_id'])?$options['store_id']:null;

        $added_coupons = isset($options['added_coupons'])?$options['added_coupons']:[];

        $shippings = isset($options['shippings'])?$options['shippings']:null;

        if($coupon_code){
            $qb->where('coupon_code', 'LIKE', $coupon_code);
        }else{
            $qb->whereNull('coupon_code');
        }

        $qb->where(function($qb) use ($currency){
            $qb->whereNull('currency');

            if($currency){
                $qb->orWhere('currency', $currency);
            }
        });

        $qb->where(function($qb) use ($store){
            $qb->whereNull('store_id');

            if($store){
                $qb->orWhere('store_id', $store);
            }
        });

        $qb->where(function($qb) use ($customer){
            $qb->whereNull('customer_id');

            if($customer){
                $qb->orWhere('customer_id', $customer->id);
            }
        });

        $qb->where(function($qb) use ($subtotal){
            $qb->whereNull('minimum_subtotal');

            if(!is_null($subtotal)){
                $qb->orWhere('minimum_subtotal', '<=', $subtotal);
            }
        });

        $qb->where(function($qb) use ($shippings){
            $qb->whereDoesntHave('shippingOptionGroup');

            if($shippings){
                $qb->orWhereHas('shippingOptionGroup.shippingMethods', function ($query) use ($shippings) {
                    $query->whereIn('id', $shippings);
                });
            }
        });

        $priceRules = $qb->get();


        foreach($added_coupons as $added_coupon){
            if(!in_array($added_coupon, $priceRules->pluck('id')->all())){
                $coupon = self::find($added_coupon);

                if($coupon){
                    $priceRules->push($coupon);
                }
            }
        }

        return $priceRules;
    }

    public static function getCouponByCode($code)
    {
        if(!$code){
            return false;
        }

        $qb = self::where('coupon_code', trim($code));

        return $qb->first();
    }
}
