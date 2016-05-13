<?php

namespace Kommercio\Models\Address;

class Country extends Address
{
    protected $table = 'address_countries';

    //Relations
    public function states()
    {
        return $this->hasMany('Kommercio\Models\Address\State')->orderBy('sort_order', 'ASC');
    }
}
