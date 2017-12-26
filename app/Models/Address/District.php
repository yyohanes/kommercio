<?php

namespace Kommercio\Models\Address;

use Kommercio\Models\Interfaces\CacheableInterface;

class District extends Address implements CacheableInterface
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

    /**
     * @inheritdoc
     */
    public function getCacheKeys()
    {
        $tableName = $this->getTable();
        $keys = [
            $tableName . '_all',
            $tableName . '_city_' . $this->city->id . '_districts',
        ];

        return $keys;
    }
}
