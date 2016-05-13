<?php

namespace Kommercio\Models;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Facades\PriceFormatter;
use Kommercio\Traits\Model\ToggleDate;

class PriceRule extends Model
{
    use ToggleDate;

    const MODIFICATION_TYPE_PERCENT = 'percent';
    const MODIFICATION_TYPE_AMOUNT = 'amount';

    protected $fillable = ['name', 'product_id', 'variation_id', 'price', 'modification', 'modification_type',
        'currency', 'store_id', 'active', 'active_date_from', 'active_date_to', 'sort_order'];
    protected $toggleFields = ['active'];
    protected $casts = [
        'active' => 'boolean'
    ];

    //Relations
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

    //Scopes
    public function scopeNotProductSpecific($query)
    {
        $query->whereNull('product_id');
    }

    //Methods
    public function getPrice()
    {
        return PriceFormatter::formatNumber($this->price, $this->currency);
    }

    public function getModification()
    {
        if($this->modification_type == 'amount'){
            return PriceFormatter::formatNumber($this->modification, $this->currency);
        }else{
            return ($this->modification+0).'%';
        }
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
}
