<?php

namespace Kommercio\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
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

    //Statics
    public static function getWarehouseOptions()
    {
        $warehouses = Warehouse::orderBy('created_at', 'DESC')->pluck('name', 'id')->all();

        return $warehouses;
    }
}
