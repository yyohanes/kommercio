<?php

namespace Kommercio\Models\Address;

use Kommercio\Models\Interfaces\CacheableInterface;

class Area extends Address implements CacheableInterface
{
    protected $table = 'address_areas';

    //Relations
    public function district()
    {
        return $this->belongsTo('Kommercio\Models\Address\District');
    }

    /**
     * @inheritdoc
     */
    public function getCacheKeys()
    {
        $tableName = $this->getTable();
        $keys = [
            $tableName . '_all',
            $tableName . '_district_' . $this->district->id . '_areas',
        ];

        return $keys;
    }
}
