<?php

namespace Kommercio\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Facades\LanguageHelper;
use Kommercio\Facades\PriceFormatter;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;
use Kommercio\Models\Order\DeliveryOrder\DeliveryOrder;
use Kommercio\Traits\Model\AuthorSignature;
use Kommercio\Traits\Model\HasDataColumn;

class OrderComment extends Model implements AuthorSignatureInterface
{
    use AuthorSignature, HasDataColumn;

    const TYPE_INTERNAL = 'internal';
    const TYPE_INTERNAL_MEMO = 'internal_memo';
    const TYPE_EXTERNAL_MEMO = 'external_memo';

    protected $guarded = [];

    // Methods
    public function printMessage()
    {
        $key = $this->getData('key');

        if($this->type === self::TYPE_EXTERNAL_MEMO){
            if($key == 'cancelled'){
                $messageOptions = [
                    'reason' => $this->getData('reason')
                ];
            }elseif(strpos($key, 'payment') !== FALSE){
                $payment = Payment::find($this->getData('payment_id'));
                $messageOptions = $payment->toArray();
                $messageOptions['amount'] = PriceFormatter::formatNumber($messageOptions['amount']);
            }elseif(strpos($key, 'shipped') !== FALSE){
                $deliveryOrder = DeliveryOrder::find($this->getData('delivery_order_id'));
                $messageOptions = array_merge($deliveryOrder->toArray(), $deliveryOrder->getData());
            }else{
                $messageOptions = [];
            }

            return trans(LanguageHelper::getTranslationKey('order.memos.external.'.$key), $messageOptions);
        }else{
            switch($key){
                default:
                    return nl2br($this->body);
            }
        }
    }

    // Relations
    public function order()
    {
        return $this->belongsTo('Kommercio\Models\Order\Order');
    }

    // Scopes
    public function scopeInternalMemo($query)
    {
        $query->whereIn('type', [self::TYPE_INTERNAL_MEMO, self::TYPE_INTERNAL]);
    }

    public function scopeExternalMemo($query)
    {
        $query->whereIn('type', [self::TYPE_EXTERNAL_MEMO]);
    }

    // Statics
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
