<?php

namespace Kommercio\Models;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    const TYPE_COMBINE = 'combine';
    const TYPE_ORDERLY = 'orderly';
    const TYPE_SINGLE = 'single';

    protected $guarded = [];
    protected $casts = [
        'shipping' => 'boolean'
    ];

    public static function getTypeOptions($option=null)
    {
        $array = [
            self::TYPE_COMBINE => 'Combine',
            self::TYPE_ORDERLY => 'One After Another',
            self::TYPE_SINGLE => 'Only This Tax',
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }
}
