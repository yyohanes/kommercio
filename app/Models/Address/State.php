<?php

namespace Kommercio\Models\Address;

class State extends Address
{
    protected $table = 'address_states';

    //Relations
    public function country()
    {
        return $this->belongsTo('Kommercio\Models\Address\Country');
    }

    public function cities()
    {
        return $this->hasMany('Kommercio\Models\Address\City')->orderBy('sort_order', 'ASC');
    }

    /**
     * @inheritdoc
     */
    public function getCacheKeys()
    {
        $tableName = $this->getTable();
        $keys = [
            $tableName . '_all',
            $tableName . '_country_' . $this->country->id . '_states',
        ];

        return array_merge(
            parent::getCacheKeys(),
            $keys
        );
    }
}
