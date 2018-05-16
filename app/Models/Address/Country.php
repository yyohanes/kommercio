<?php

namespace Kommercio\Models\Address;

use Illuminate\Support\Facades\Cache;
use Kommercio\Models\Interfaces\CacheableInterface;

class Country extends Address
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
            $tableName . '_iso_code_' . $this->iso_code,
            $tableName . '_all',
        ];

        return array_merge(
            parent::getCacheKeys(),
            $keys
        );
    }

    // Statics
    public static function findByIsoCode($code) {
        $tableName = (new static)->getTable();

        return Cache::rememberForever($tableName . '_iso_code_' . $code, function() use ($code) {
            return static::where('iso_code', $code)->first();
        });
    }
}
