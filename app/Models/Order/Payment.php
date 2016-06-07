<?php

namespace Kommercio\Models\Order;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;
use Kommercio\Traits\Model\AuthorSignature;
use Kommercio\Traits\Model\HasDataColumn;

class Payment extends Model implements AuthorSignatureInterface
{
    use AuthorSignature, HasDataColumn;

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
    public function recordStatusChange($status, $by, $note=null)
    {
        $history = $this->getData('actions');

        if(!$history){
            $history = [];
        }

        $history[] = [
            'status' => self::getStatusOptions($status),
            'by' => $by,
            'at' => Carbon::now()->toDateTimeString(),
            'notes' => $note
        ];

        $this->saveData(['history' => $history]);
    }

    public function getHistory()
    {
        $histories = $this->getData('history');

        if(!is_array($histories)){
            $histories = $histories?[$histories]:[];
        }

        return $histories;
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
