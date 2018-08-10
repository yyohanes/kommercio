<?php

namespace Kommercio\Models\Order;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Interfaces\CacheableInterface;
use Kommercio\Models\Interfaces\StoreManagedInterface;
use Kommercio\Models\Product;
use Kommercio\Models\User;

class OrderLimit extends Model implements StoreManagedInterface, CacheableInterface
{
    const TYPE_PRODUCT = 'product';
    const TYPE_PRODUCT_CATEGORY = 'product_category';

    const LIMIT_ORDER_DATE = 'checkout_at';
    const LIMIT_DELIVERY_DATE = 'delivery_date';
    const LIMIT_PER_ORDER = 'per_order';
    const LIMIT_DELIVERY_DATE_RANGE = 'delivery_date_range';

    /**
     * @var int Total of counted products
     */
    public $total = 0;

    public $fillable = ['type', 'limit_type', 'limit', 'date_from', 'date_to', 'active', 'backoffice', 'store_id', 'sort_order'];
    protected $casts = [
        'active' => 'boolean',
        'backoffice' => 'boolean'
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
            $intersect = $this->productCategories->pluck('id')->intersect($product->categories->pluck('id'));
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

    public function getCacheKeys()
    {
        return [
            ['order_limits'],
        ];
    }

    //Scopes
    public function scopeBackoffice($query)
    {
        $query->where('backoffice', true);
    }

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
        if (is_array($limit_type)) {
            $query->whereIn('limit_type', $limit_type);
        } else {
            $query->where('limit_type', $limit_type);
        }
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
            $array[self::LIMIT_DELIVERY_DATE_RANGE] = 'Total per Delivery Date Range';
        }

        if (empty($option)) {
            return $array;
        }

        return (isset($array[$option])) ? $array[$option] : $array;
    }

    public static function getOrderLimits($options)
    {
        if (ProjectHelper::cacheIsTaggable()) {
            $hashOptions = $options;

            // Date constantly changing, force it for consistency
            if (!empty($hashOptions['date'])) {
                $hashOptions['date'] = $hashOptions['date']->setTime(0, 0, 0);
            }

            if (!empty($hashOptions['product'])) {
                $hashOptions['product'] = $hashOptions['product']->id;
            }

            $hash = ProjectHelper::flattenArrayToKey($hashOptions);

            $orderLimits = Cache::tags(['order_limits'])->rememberForever($hash, function() use ($options) {
                return static::_getOrderLimits($options);
            });

            return $orderLimits;
        }

        return static::_getOrderLimits($options);
    }

    protected static function _getOrderLimits($options)
    {
        $qb = OrderLimit::active()
            ->orderBy('sort_order', 'ASC');

        // TODO: Remove backoffice option all together from backoffice
        if(isset($options['backoffice'])){
            // $qb->backoffice();
        }

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

        if(!empty($options['category'])){
            $qb->whereHas('productCategories', function($qb) use ($options){
                $qb->whereIn('id', [$options['category']->id]);
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
            if(isset($date)){
                if($orderLimit->dayRulesPassed($date)){
                    $orderLimits[] = $orderLimit;
                }
            }else{
                $orderLimits[] = $orderLimit;
            }
        }

        return $orderLimits;
    }
}
