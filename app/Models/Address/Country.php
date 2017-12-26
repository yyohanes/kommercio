<?php

namespace Kommercio\Models\Address;

use Kommercio\Models\Interfaces\CacheableInterface;

class Country extends Address implements CacheableInterface
{
    protected $table = 'address_countries';

    //Relations
    public function states()
    {
        return $this->hasMany('Kommercio\Models\Address\State')->orderBy('sort_order', 'ASC');
    }

    /**
     * @inheritdoc
     */
    public function getCacheKeys()
    {
        $tableName = $this->getTable();
        $keys = [
            $tableName . '_all',
        ];

        return $keys;
    }
}
