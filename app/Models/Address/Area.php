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

        return array_merge(
            parent::getCacheKeys(),
            $keys
        );
    }
}
