<?php

namespace Kommercio\Models\Order;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Interfaces\StoreManagedInterface;
use Kommercio\Models\Product;
use Kommercio\Models\User;

class OrderLimit extends Model implements StoreManagedInterface
{
    const TYPE_PRODUCT = 'product';
    const TYPE_PRODUCT_CATEGORY = 'product_category';

    const LIMIT_ORDER_DATE = 'checkout_at';
    const LIMIT_DELIVERY_DATE = 'delivery_date';
    const LIMIT_PER_ORDER = 'per_order';

    /**
     * @var int Total of counted products
     */
    public $total = 0;

    protected $fillable = ['type', 'limit_type', 'limit', 'date_from', 'date_to', 'active', 'store_id', 'sort_order'];
    protected $casts = [
        'active' => 'boolean'
    ];
    protected $dates = [
        'date_from', 'date_to'
    ];

    //Methods
    public function hasDate()
    {
        return !empty($this->date_from) || !empty($this->date_to);
    }

    public function dayRulesPassed(Carbon $date)
    {
        if ($this->dayRules->count() < 1) {
            return TRUE;
        }

        $valid = false;

        foreach ($this->dayRules as $dayRule) {
            $valid = $dayRule->check($date);

            if ($valid) {
                break;
            }
        }

        return $valid;
    }

    /**
     * Check if product is included
     * @param Product $product Product to validate
     * @return bool
     */
    public function productRulesPassed(Product $product)
    {
        $return = false;

        if($this->products->count() > 0){
            $return = $this->products->pluck('id')->contains($product->id);
        }

        if(!$return && $this->productCategories->count() > 0){
            $intersect = $this->productCategories->intersect($product->categories);
            $return = $intersect->count() > 0;
        }

        return $return;
    }

    public function checkStorePermissionByUser(User $user)
    {
        if ($user->manageAllStores) {
            return true;
        }

        return $this->store_id && in_array($this->store_id, $user->getManagedStores()->pluck('id')->all());
    }

    //Scopes
    public function scopeActive($query)
    {
        $query->where('active', 1);
    }

    public function scopeWhereStore($query, $store_id)
    {
        $query->where(function ($query) use ($store_id) {
            $query->whereNull('store_id')->orWhereIn('store_id', [$store_id]);
        });
    }

    public function scopeWhereProduct($query, $product_id)
    {
        $query->whereHas('products', function ($query) use ($product_id) {
            $query->whereIn('id', [$product_id]);
        });
    }

    public function scopeWhereProductCategories($query, $categories)
    {
        $query->whereHas('productCategories', function ($query) use ($categories) {
            $query->whereIn('id', $categories);
        });
    }

    public function scopeWhereType($query, $type)
    {
        $query->where('type', $type);
    }

    public function scopeWhereLimitType($query, $limit_type)
    {
        $query->where('limit_type', $limit_type);
    }

    public function scopeWithinDate($qb, Carbon $date)
    {
        $qb->where(function ($query) use ($date) {
            $query->whereNull('date_from')->orWhere('date_from', '<=', $date->format('Y-m-d H:i:s'));
        });

        $qb->where(function ($query) use ($date) {
            $query->whereNull('date_to')->orWhere('date_to', '>=', $date->format('Y-m-d H:i:s'));
        });
    }

    public function scopeAllDays($query)
    {
        $query->whereNull('date_from')->whereNull('date_to');
    }

    //Relations
    public function store()
    {
        return $this->belongsTo('Kommercio\Models\Store');
    }

    public function products()
    {
        return $this->morphedByMany('Kommercio\Models\Product', 'order_limitable')->withTranslation();
    }

    public function productCategories()
    {
        return $this->morphedByMany('Kommercio\Models\ProductCategory', 'order_limitable')->withTranslation();
    }

    public function dayRules()
    {
        return $this->hasMany('Kommercio\Models\Order\OrderLimitDayRule');
    }

    public function getItemRelation()
    {
        switch ($this->type) {
            case self::TYPE_PRODUCT_CATEGORY:
                return $this->productCategories();
                break;
            default:
                return $this->products();
                break;
        }
    }

    public function getItems()
    {
        switch ($this->type) {
            case self::TYPE_PRODUCT_CATEGORY:
                return $this->productCategories;
                break;
            default:
                return $this->products;
                break;
        }
    }

    //Mutators
    public function setDateFromAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['date_from'] = NULL;
        } else {
            $this->attributes['date_from'] = Carbon::createFromFormat('Y-m-d H:i', $value);
        }

        return $this;
    }

    public function setDateToAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['date_to'] = NULL;
        } else {
            $this->attributes['date_to'] = Carbon::createFromFormat('Y-m-d H:i', $value);
        }

        return $this;
    }

    //Statics
    public static function getTypeOptions($option = null)
    {
        $array = [
            self::TYPE_PRODUCT => 'Product',
            self::TYPE_PRODUCT_CATEGORY => 'Product Category',
        ];

        if (empty($option)) {
            return $array;
        }

        return (isset($array[$option])) ? $array[$option] : $array;
    }

    public static function getLimitTypeOptions($option = null)
    {
        $array = [
            self::LIMIT_PER_ORDER => 'Per Order',
            self::LIMIT_ORDER_DATE => 'Total per Day',
        ];

        if (config('project.enable_delivery_date')) {
            $array[self::LIMIT_DELIVERY_DATE] = 'Total per Delivery Date';
        }

        if (empty($option)) {
            return $array;
        }

        return (isset($array[$option])) ? $array[$option] : $array;
    }

    public static function getOrderLimits($options)
    {
        $qb = OrderLimit::active()
            ->orderBy('sort_order', 'ASC');

        if(!empty($options['limit_type'])){
            $qb->whereLimitType($options['limit_type']);
        }

        if(!empty($options['type'])){
            $qb->whereType($options['type']);
        }

        if(!empty($options['product'])){
            $qb->where(function($qb) use ($options){
                $qb->whereHas('products', function($qb) use ($options){
                    $qb->whereIn('id', [$options['product']->id]);
                })
                    ->orWhereHas('productCategories', function($qb) use ($options){
                        $qb->whereIn('id', $options['product']->categories->pluck('id')->all());
                    });
            });
        }

        if(!empty($options['store'])){
            $qb->whereStore($options['store']);
        }

        if(!empty($options['date'])){
            $date = $options['date'];
            $qb->withinDate($date);
        }else{
            $date = Carbon::now();
            $qb->allDays();
        }

        $orderLimits = [];

        foreach($qb->get() as $orderLimit){
            if(isset($date) && $orderLimit->dayRulesPassed($date)){
                $orderLimits[] = $orderLimit;
            }else{
                $orderLimits[] = $orderLimit;
            }
        }

        return $orderLimits;
    }
}
