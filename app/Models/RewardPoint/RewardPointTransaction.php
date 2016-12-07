<?php

namespace Kommercio\Models\RewardPoint;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;
use Kommercio\Traits\Model\AuthorSignature;
use Kommercio\Traits\Model\HasDataColumn;

class RewardPointTransaction extends Model implements AuthorSignatureInterface
{
    use AuthorSignature, HasDataColumn;

    const STATUS_APPROVED = 'approved';
    const STATUS_REVIEW = 'review';
    const STATUS_DECLINED = 'declined';

    const TYPE_ADD = 'add';
    const TYPE_DEDUCT = 'deduct';

    protected $fillable = ['type', 'status', 'amount', 'reason', 'notes', 'data'];
    protected $dates = ['approved_at'];

    //Relations
    public function customer()
    {
        return $this->belongsTo('Kommercio\Models\Customer');
    }

    public function order()
    {
        return $this->belongsTo('Kommercio\Models\Order\Order');
    }

    //Static
    public static function getStatusOptions($option=null, $all=false)
    {
        $array = [
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_DECLINED => 'Declined',
            self::STATUS_REVIEW => 'Review',
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }

    public static function getTypeOptions($option=null, $all=false)
    {
        $array = [
            self::TYPE_ADD => 'Add',
            self::TYPE_DEDUCT => 'Deduct',
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }
}
