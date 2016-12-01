<?php

namespace Kommercio\Models\RewardPoint;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;
use Kommercio\Traits\Model\AuthorSignature;
use Kommercio\Traits\Model\HasDataColumn;

class RewardRule extends Model implements AuthorSignatureInterface
{
    use AuthorSignature, HasDataColumn;

    const TYPE_PER_ORDER = 'per_order';

    protected $fillable = ['name', 'type', 'reward', 'currency', 'date_from', 'date_to', 'sort_order', 'data'];
    protected $dates = ['date_from', 'date_to'];

    //Relations
    public function store()
    {
        return $this->belongsTo('Kommercio\Models\Store');
    }

    //Static
    public static function getTypeOptions($option=null, $all=false)
    {
        $array = [
            self::TYPE_PER_ORDER => 'Per Order',
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }
}
