<?php

namespace Kommercio\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Kommercio\Traits\Model\HasDataColumn;

class Store extends Model
{
    use HasDataColumn;

    const TYPE_ONLINE = 'online';
    const TYPE_OFFLINE = 'offline';

    protected $guarded = ['warehouses', 'contacts'];

    //Relations
    public function orders()
    {
        return $this->hasMany('Kommercio\Models\Order\Order');
    }

    //Accessors
    public function getOrderCountAttribute()
    {
        $count = $this->orders()->checkout()->count();

        return $count;
    }

    public function getProductCountAttribute()
    {
        return $this->productDetails->count();
    }

    //Methods
    public function getDefaultWarehouse()
    {
        $warehouse = $this->warehouses->get(0);

        return $warehouse;
    }

    public function getTaxes()
    {
        $taxes = Tax::getTaxes([
            'store_id' => $this->id,
        ]);

        return $taxes;
    }

    //Static
    public static function getTypeOptions($option=null)
    {
        $array = [
            self::TYPE_ONLINE => 'Online',
            self::TYPE_OFFLINE => 'Offline',
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }

    public static function getDefaultStore()
    {
        $defaultStore = self::where('default', true)->first();

        if(!$defaultStore){
            $defaultStore = self::orderBy('created_at', 'ASC')->first();
        }

        return $defaultStore;
    }

    public static function getStoreOptions($all = false, $withAllOption = FALSE)
    {
        $stores = [];

        if($withAllOption){
            $stores += ['all' => 'All'];
        }

        if($all){
            $stores += self::orderBy('created_at', 'DESC')->pluck('name', 'id')->all();
        }else{
            $stores += Auth::user()->getManagedStores()->pluck('name', 'id')->all();
        }

        return $stores;
    }

    //Relations
    public function warehouses()
    {
        return $this->belongsToMany('Kommercio\Models\Warehouse')->withPivot('sort_order')->orderBy('sort_order', 'ASC');
    }

    public function productDetails()
    {
        return $this->hasMany('Kommercio\Models\ProductDetail')->productEntity();
    }

    public function orderLimits()
    {
        return $this->hasMany('Kommercio\Models\Order\OrderLimit');
    }

    public function users()
    {
        return $this->belongsToMany('Kommercio\Models\User');
    }
}
