<?php

namespace Kommercio\Models;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Facades\PriceFormatter;
use Kommercio\Models\Interfaces\StoreManagedInterface;
use Kommercio\Traits\Model\ToggleDate;

class PriceRule extends Model implements StoreManagedInterface
{
    use ToggleDate;

    const MODIFICATION_TYPE_PERCENT = 'percent';
    const MODIFICATION_TYPE_AMOUNT = 'amount';

    protected $fillable = ['name', 'product_id', 'variation_id', 'price', 'modification', 'modification_type',
        'currency', 'store_id', 'active', 'active_date_from', 'active_date_to', 'is_discount', 'sort_order'];
    protected $toggleFields = ['active'];
    protected $casts = [
        'active' => 'boolean',
        'is_discount' => 'boolean'
    ];

    private $_products;

    // Methods
    public function getProducts()
    {
        if(!isset($this->_products)){
            if($this->product){
                $this->_products = [$this->product];
            }else{
                foreach($this->priceRuleOptionGroups as $priceRuleOptionGroup){
                    foreach($priceRuleOptionGroup->getProducts() as $product){
                        $this->_products[$product->id] = $product;
                    }
                }
            }
        }

        return $this->_products;
    }

    // Relations
    public function product()
    {
        return $this->belongsTo('Kommercio\Models\Product');
    }

    public function variation()
    {
        return $this->belongsTo('Kommercio\Models\Product', 'variation_id');
    }

    public function store()
    {
        return $this->belongsTo('Kommercio\Models\Store');
    }

    public function priceRuleOptionGroups()
    {
        return $this->hasMany('Kommercio\Models\PriceRuleOptionGroup');
    }

    // Scopes
    public function scopeNotProductSpecific($query)
    {
        $query->whereNull('product_id');
    }

    public function scopeIsDiscount($query)
    {
        $query->where('is_discount', 1);
    }

    public function scopeIsNotDiscount($query)
    {
        $query->where(function($query){
            $query->where('is_discount', 0)->orWhereNull('is_discount');
        });
    }

    public function scopeActive($query)
    {
        $query->where('active', 1);
    }

    // Methods
    public function getValue($price = null)
    {
        $return = 0;

        if(!is_null($this->price)){
            $return = $this->price;
        }else{
            if(is_null($price)){
                $price = $this->price;
            }

            if($this->modification_type == 'amount'){
                $return = $price + $this->modification;
            }else{
                $return = $price + ($price * $this->modification / 100);
            }
        }

        return PriceFormatter::round($return);
    }

    public function getModificationOutput()
    {
        if($this->modification_type == 'amount'){
            return PriceFormatter::formatNumber($this->modification, $this->currency);
        }else{
            return ($this->modification+0).'%';
        }
    }

    public function checkStorePermissionByUser(User $user)
    {
        if($user->manageAllStores){
            return true;
        }

        return $this->store_id && in_array($this->store_id, $user->getManagedStores()->pluck('id')->all());
    }

    // Statics
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
}
