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
    const TYPE_INTERNAL_MEMO = 'internal_memo';
    const TYPE_EXTERNAL_MEMO = 'external_memo';

    protected $guarded = [];

    //Relations
    public function order()
    {
        return $this->belongsTo('Kommercio\Models\Order\Order');
    }

    //Scopes
    public function scopeInternalMemo($query)
    {
        $query->whereIn('type', [self::TYPE_INTERNAL_MEMO, self::TYPE_INTERNAL]);
    }

    //Statics
    public static function getTypeOptions($option=null)
    {
        $array = [
            self::TYPE_EXTERNAL_MEMO => 'External Memo',
            self::TYPE_INTERNAL_MEMO => 'Internal Memo',
            self::TYPE_INTERNAL => 'Internal',
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }
}
