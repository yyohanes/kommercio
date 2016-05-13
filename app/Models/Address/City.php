<?php

namespace Kommercio\Models\Address;

class City extends Address
{
    protected $table = 'address_cities';

    //Relations
    public function state()
    {
        return $this->belongsTo('Kommercio\Models\Address\State');
    }

    public function districts()
    {
        return $this->hasMany('Kommercio\Models\Address\Disctrict')->orderBy('sort_order', 'ASC');
    }
}
