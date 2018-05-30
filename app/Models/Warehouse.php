<?php

namespace Kommercio\Models;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Traits\Model\HasAddress;

class Warehouse extends Model
{
    use HasAddress;

    protected $guarded = ['location'];

    //Relations
    public function stores()
    {
        return $this->belongsToMany('Kommercio\Models\Store');
    }

    public function products()
    {
        return $this->belongsToMany('Kommercio\Models\Product')->withPivot('stock');
    }

    //Accessors
    public function getProductCountAttribute()
    {
        return $this->products()->wherePivot('stock', '>', 0)->count();
    }

    public function getLocationAttribute()
    {
        return [
            'address_1' => $this->address_1,
            'address_2' => $this->address_2,
            'country_id' => $this->country_id,
            'state_id' => $this->state_id,
            'city_id' => $this->city_id,
            'district_id' => $this->district_id,
            'area_id' => $this->area_id,
            'postal_code' => $this->postal_code,
            'custom_city' => $this->custom_city,
        ];
    }

    //Statics
    public static function getWarehouseOptions()
    {
        $warehouses = Warehouse::orderBy('created_at', 'DESC')->pluck('name', 'id')->all();

        return $warehouses;
    }
}
