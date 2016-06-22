<?php

namespace Kommercio\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;
use Kommercio\Traits\Model\AuthorSignature;
use Kommercio\Traits\Model\HasDataColumn;

class OrderComment extends Model implements AuthorSignatureInterface
{
    use AuthorSignature, HasDataColumn;

    const TYPE_INTERNAL = 'internal';
    const TYPE_EXTERNAL = 'external';

    protected $guarded = [];

    //Relations
    public function order()
    {
        return $this->belongsTo('Kommercio\Models\Order\Order');
    }

    //Statics
    public static function getTypeOptions($option=null)
    {
        $array = [
            self::TYPE_EXTERNAL => 'External',
            self::TYPE_INTERNAL => 'Internal',
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }
}
