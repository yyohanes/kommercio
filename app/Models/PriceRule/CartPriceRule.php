<?php

namespace Kommercio\Models\PriceRule;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Facades\LanguageHelper;
use Kommercio\Facades\OrderHelper;
use Kommercio\Facades\PriceFormatter;
use Kommercio\Models\Customer;
use Kommercio\Models\Interfaces\StoreManagedInterface;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Product;
use Kommercio\Models\User;
use Kommercio\Traits\Model\ToggleDate;

class CartPriceRule extends Model implements StoreManagedInterface
{
    use ToggleDate;

    const MODIFICATION_TYPE_PERCENT = 'percent';
    const MODIFICATION_TYPE_AMOUNT = 'amount';

    const MODIFICATION_SOURCE_BASE = 0;
    const MODIFICATION_SOURCE_NET = 1;

    const OFFER_TYPE_FREE_SHIPPING = 'free_shipping';
    const OFFER_TYPE_ORDER_DISCOUNT = 'order_discount';
    const OFFER_TYPE_PRODUCT_DISCOUNT = 'product_discount';

    protected $fillable = ['name', 'price', 'modification', 'modification_type', 'modification_source',
        'currency', 'store_id', 'customer_id', 'minimum_subtotal', 'max_usage', 'max_usage_per_customer', 'offer_type', 'active', 'active_date_from', 'active_date_to', 'sort_order'];
    protected $toggleFields = ['active'];
    protected $casts = [
        'active' => 'boolean',
    ];

    private $_couponCode = null;

    //To store calculation
    public $total = 0;
    public $appliedLineItems = [];

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

    public function coupons()
    {
        return $this->hasMany('Kommercio\Models\PriceRule\Coupon');
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

        return $calculatedAmount;
    }

    public function getNetValue($price = null)
    {
        $calculatedAmount = $price;

        if($this->modification_type == self::MODIFICATION_TYPE_AMOUNT && !is_null($this->modification)){
            $calculatedAmount = $this->modification;
        }elseif($this->modification_type == self::MODIFICATION_TYPE_PERCENT && !is_null($this->modification)){
            $calculatedAmount = ($calculatedAmount * $this->modification/100);
        }

        return $calculatedAmount;
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
        $type = $this->determineLineItemType();

        $qb = Order::whereHasLineItem($this->id, $type)->usageCounted();

        return $qb->count();
    }

    public function getUsageByEmail($email)
    {
        $customer = Customer::getByEmail($email);

        if(!$customer){
            return 0;
        }

        $type = $this->determineLineItemType();

        $qb = Order::whereHasLineItem($this->id, $type)->usageCounted()->where('customer_id', $customer->id);

        return $qb->count();
    }

    /*
     * TODO: Validate by coupon. Currently by cart price rule eventhough Coupon is applied
     */
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

    public function checkStorePermissionByUser(User $user)
    {
        if($user->manageAllStores){
            return true;
        }

        return $this->store_id && in_array($this->store_id, $user->getManagedStores()->pluck('id')->all());
    }

    public function linkCouponCode($coupon_code)
    {
        $this->_couponCode = $coupon_code;
    }

    protected function determineLineItemType()
    {
        $type = 'cart_price_rule';

        if($this->isCoupon){
            $type = 'coupon';
        }elseif($this->isFreeShipping) {
            $type = 'free_shipping';
        }

        return $type;
    }

    //Accessors
    public function getIsCouponAttribute()
    {
        return $this->coupons->count() > 0;
    }

    public function getIsFreeShippingAttribute()
    {
        return $this->offer_type == self::OFFER_TYPE_FREE_SHIPPING;
    }

    public function getCouponCodeAttribute()
    {
        return $this->_couponCode;
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
            self::OFFER_TYPE_FREE_SHIPPING => 'Free Shipping',
            self::OFFER_TYPE_ORDER_DISCOUNT => 'Order Discount',
            self::OFFER_TYPE_PRODUCT_DISCOUNT => 'Product Discount',
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }

    public static function getModificationSourceOptions($option=null)
    {
        $array = [
            self::MODIFICATION_SOURCE_BASE => 'Base Price',
            self::MODIFICATION_SOURCE_NET => 'Net Price',
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }

    public static function getCartPriceRules($options)
    {
        $qb = self::active()->orderBy('sort_order', 'ASC');

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
            $qb->whereHas('coupons', function($query) use ($coupon_code){
                $query->where('coupon_code', 'LIKE', trim($coupon_code));
            });
        }else{
            $qb->whereDoesntHave('coupons');
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

        if($added_coupons){
            $addedCoupons = self::whereIn('id', $added_coupons)->get();

            $priceRules = $priceRules->merge($addedCoupons)->sortBy('sort_order');
        }

        return $priceRules;
    }

    public static function getCouponByCode($code)
    {
        if(!$code){
            return false;
        }

        $qb = self::whereHas('coupons', function($query) use ($code){
            $query->where('coupon_code', trim($code));
        });

        $coupon = $qb->first();

        if($coupon){
            $coupon->linkCouponCode(trim($code));
        }

        return $coupon;
    }

    public static function getCoupon($couponCode, Order $currentOrder = null, $request = null)
    {
        if(!$request && !$currentOrder){
            throw new \Exception('You must supply either Request or Order.');
        }

        $addedCoupons = [];

        if($currentOrder){
            foreach($currentOrder->getCouponLineItems() as $couponLineItem){
                $addedCoupons[] = $couponLineItem->line_item_id;
            }
        }

        $addedCoupons = array_unique(array_merge($addedCoupons, ($request?$request->input('added_coupons', []):[])));

        $coupon = self::getCouponByCode($couponCode);
        if(!$coupon){
            return trans(LanguageHelper::getTranslationKey('order.coupons.not_exist'), ['coupon_code' => $couponCode]);
        }

        $order = $currentOrder?clone $currentOrder:OrderHelper::createDummyOrderFromRequest($request);

        $subtotal = $order->calculateProductTotal() + $order->calculateAdditionalTotal();

        $shippings = [];

        foreach($order->getShippingLineItems() as $shippingLineItem){
            $shippings[] = $shippingLineItem->line_item_id;
        }

        $options = [
            'subtotal' => $subtotal,
            'currency' => empty($order->currency)?null:$order->currency,
            'store_id' => $order->store_id,
            'customer_email' => $order->customer?$order->customer->getProfile()->email:null,
            'shippings' => $shippings,
            'coupon_code' => $couponCode,
            'added_coupons' => $addedCoupons
        ];

        $couponPriceRules = self::getCartPriceRules($options);

        if($couponPriceRules->count() < 1){
            return trans(LanguageHelper::getTranslationKey('order.coupons.invalid'), ['coupon_code' => $couponCode]);
        }else{
            foreach($couponPriceRules as $couponPriceRule){
                $couponValidation = $couponPriceRule->validateUsage($options['customer_email']);
                if(!$couponValidation['valid']){
                    return trans(LanguageHelper::getTranslationKey($couponValidation['message']), ['coupon_code' => $couponCode]);
                }
            }
        }

        return $couponPriceRules;
    }
}
