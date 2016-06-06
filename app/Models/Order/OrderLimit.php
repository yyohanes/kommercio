<?php

namespace Kommercio\Models\Order;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Product;

class OrderLimit extends Model
{
    const TYPE_PRODUCT = 'product';
    const TYPE_PRODUCT_CATEGORY = 'product_category';

    const LIMIT_ORDER_DATE = 'checkout_at';
    const LIMIT_DELIVERY_DATE = 'delivery_date';
    const LIMIT_PER_ORDER = 'per_order';

    protected $fillable = ['type', 'limit_type', 'limit', 'date_from', 'date_to', 'active', 'store_id'];
    protected $casts = [
        'active' => 'boolean'
    ];
    protected $dates = [
        'date_from', 'date_to'
    ];

    //Scopes
    public function scopeWhereProduct($query, $product_id)
    {
        $query->whereHas('products', function($query) use ($product_id){
            $query->whereIn('id', [$product_id]);
        });
    }

    public function scopeWhereProductCategories($query, $categories)
    {
        $query->whereHas('productCategories', function($query) use ($categories){
            $query->whereIn('id', $categories);
        });
    }

    public function scopeWhereLimitType($query, $limit_type)
    {
        if($limit_type == self::LIMIT_PER_ORDER){
            $query->where('limit_type', $limit_type);
        }else{
            $query->where(function($query) use ($limit_type){
                $query->where('limit_type', $limit_type)->orWhere(function($query){
                    $query->whereNull('date_from')->whereNull('date_to');
                });
            });
        }
    }

    public function scopeWithinDate($qb, Carbon $date)
    {
        $fromDate = clone $date;
        $fromDate->setTime(0, 0, 0);

        $toDate = clone $date;
        $toDate->modify('+1 day')->setTime(0, 0, 0);

        $qb->where(function($query) use ($fromDate){
            $query->whereNull('date_from')->orWhere('date_from', '<=', $fromDate);
        });

        $qb->where(function($query) use ($toDate){
            $query->whereNull('date_to')->orWhere('date_to', '>', $toDate);
        });
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

    public function getItemRelation()
    {
        switch($this->type){
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
        switch($this->type){
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
        if(empty($value)){
            $this->attributes['date_from'] = NULL;
        }else{
            $this->attributes['date_from'] = Carbon::createFromFormat('Y-m-d H:i', $value);
        }

        return $this;
    }

    public function setDateToAttribute($value)
    {
        if(empty($value)){
            $this->attributes['date_to'] = NULL;
        }else{
            $this->attributes['date_to'] = Carbon::createFromFormat('Y-m-d H:i', $value);
        }

        return $this;
    }

    //Statics
    public static function getTypeOptions($option=null)
    {
        $array = [
            self::TYPE_PRODUCT => 'Product',
            self::TYPE_PRODUCT_CATEGORY => 'Product Category',
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }

    public static function getLimitTypeOptions($option=null)
    {
        $array = [
            self::LIMIT_PER_ORDER => 'Per Order',
            self::LIMIT_ORDER_DATE => 'Order Date',
        ];

        if(config('project.enable_delivery_date')){
            $array[self::LIMIT_DELIVERY_DATE] = 'Delivery Date';
        }

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }

    public static function getProductOrderLimits(Product $product, Carbon $date, $limit_type)
    {
        $qb = self::whereProduct($product->id)->withinDate($date)->whereLimitType($limit_type);
        $orderLimits = $qb->get();

        return $orderLimits;
    }

    public static function getCategoryOrderLimits(Product $product, Carbon $date, $limit_type)
    {
        $qb = self::whereProductCategories($product->categories->pluck('id')->all())->withinDate($date)->whereLimitType($limit_type);
        $orderLimits = $qb->get();

        return $orderLimits;
    }
}
