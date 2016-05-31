<?php

namespace Kommercio\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;
use Kommercio\Traits\Model\AuthorSignature;

class Payment extends Model implements AuthorSignatureInterface
{
    use AuthorSignature;

    const STATUS_VOID = 'void';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_REVIEW = 'review';
    const STATUS_PENDING = 'pending';

    protected $guarded = [];

    //Scope
    public function scopeSuccessful($query)
    {
        $query->whereIn('status', [self::STATUS_SUCCESS]);
    }

    //Relations
    public function order()
    {
        return $this->belongsTo('Kommercio\Models\Order\Order');
    }

    public function paymentMethod()
    {
        return $this->belongsTo('Kommercio\Models\PaymentMethod\PaymentMethod');
    }

    //Methods
    public function saveData($data)
    {
        $oldData = unserialize($this->data);
        $oldData = $oldData?$oldData:[];

        $data = array_merge($oldData, $data);

        $this->data = serialize($data);
    }

    public function getData($attribute=null)
    {
        $data = unserialize($this->data);

        if($attribute){
            return isset($data[$attribute])?$data[$attribute]:null;
        }

        return $data;
    }

    //Accessors
    public function getIsSuccessAttribute()
    {
        return $this->status == self::STATUS_SUCCESS;
    }

    //Statics
    public static function getStatusOptions($option=null)
    {
        $array = [
            self::STATUS_SUCCESS => 'Success',
            self::STATUS_REVIEW => 'Review',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_VOID => 'Void',
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }
}
