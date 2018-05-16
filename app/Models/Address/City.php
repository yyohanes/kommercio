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
        return $this->hasMany('Kommercio\Models\Address\District')->orderBy('sort_order', 'ASC');
    }

    /**
     * @inheritdoc
     */
    public function getCacheKeys()
    {
        $tableName = $this->getTable();
        $keys = [
            $tableName . '_all',
            $tableName . '_state_' . $this->state->id . '_cities',
        ];

        return array_merge(
            parent::getCacheKeys(),
            $keys
        );
    }
}
