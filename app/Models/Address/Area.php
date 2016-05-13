<?php

namespace Kommercio\Models\Address;

class Area extends Address
{
    protected $table = 'address_areas';

    //Relations
    public function district()
    {
        return $this->belongsTo('Kommercio\Models\Address\District');
    }
}
