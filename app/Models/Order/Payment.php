<?php

namespace Kommercio\Models\Order;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Kommercio\Events\PaymentEvent;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;
use Kommercio\Models\PaymentMethod\PaymentMethod;
use Kommercio\Traits\Model\AuthorSignature;
use Kommercio\Traits\Model\HasDataColumn;
use Kommercio\Traits\Model\MediaAttachable;

class Payment extends Model implements AuthorSignatureInterface
{
    use AuthorSignature, HasDataColumn, MediaAttachable;

    const STATUS_VOID = 'void';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_REVIEW = 'review';
    const STATUS_PENDING = 'pending';

    protected $guarded = [];
    protected $dates = ['payment_date'];

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

    public function invoice()
    {
        return $this->belongsTo('Kommercio\Models\Order\Invoice');
    }

    public function paymentMethod()
    {
        return $this->belongsTo('Kommercio\Models\PaymentMethod\PaymentMethod');
    }

    //Relations
    public function attachments()
    {
        return $this->media('attachment');
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

    /**
     * Create Order Payment
     * @param Order $order
     * @param string $status
     * @param PaymentMethod|null $paymentMethod
     * @param string $notes
     * @param array $options
     * @return Payment
     */
    public static function createPayment(Order $order, Invoice $invoice = null, $status = self::STATUS_PENDING, PaymentMethod $paymentMethod = null, $notes = '', $options = [])
    {
        $paymentData = [
            'amount' => $order->total,
            'currency' => $order->currency,
            'status' => $status,
            'notes' => $notes
        ];

        $payment = new self();
        $payment->fill($paymentData);

        if(!$paymentMethod){
            $paymentMethod = $order->paymentMethod;
        }

        if(!$invoice){
            $invoice = $order->invoices->get(0);
        }

        //Create invoice if no longer created
        if(!$invoice){
            $invoice = Invoice::createInvoice($order);
        }

        $payment->invoice()->associate($invoice);
        $payment->order()->associate($order);
        $payment->paymentMethod()->associate($paymentMethod);

        if(!empty($options['payment_date'])){
            $payment->payment_date = $options['payment_date'];
        }else{
            $payment->payment_date = Carbon::now();
        }

        if(!empty($options['data'])){
            $payment->saveData($options['data']);
        }

        $payment->save();

        if($payment->status == self::STATUS_SUCCESS){
            Event::fire(new PaymentEvent('accept', $payment));
        }

        return $payment;
    }

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
