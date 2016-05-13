<?php

namespace Kommercio\Models\Address;

class District extends Address
{
    protected $table = 'address_districts';

    //Relations
    public function city()
    {
        return $this->belongsTo('Kommercio\Models\Address\City');
    }

    public function areas()
    {
        return $this->hasMany('Kommercio\Models\Address\Area')->orderBy('sort_order', 'ASC');
    }
}
