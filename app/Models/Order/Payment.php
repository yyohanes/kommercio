<?php

namespace Kommercio\Models\Order;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Kommercio\Events\PaymentEvent;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;
use Kommercio\Models\Log;
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
    const STATUS_INITIATE = 'initiate';

    protected $guarded = [];
    protected $dates = ['payment_date'];

    //Scope
    public function scopeCounted($query)
    {
        $query->whereNotIn('status', [self::STATUS_INITIATE]);
    }

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

    public function logs()
    {
        return $this->morphMany('Kommercio\Models\Log', 'loggable');
    }

    //Relations
    public function attachments()
    {
        return $this->media('attachment');
    }

    //Methods
    public function changeStatus($status, $note=null, $by = null, $data = null)
    {
        $oldStatus = $this->status;
        $this->status = $status;
        $this->save();

        if($oldStatus != $status && $status == self::STATUS_SUCCESS){
            Event::fire(new PaymentEvent('accept', $this));
        }

        if(!$by){
            $by = Auth::user()->email;
        }
        $this->recordStatusChange($status, $by, $note, $data);
    }

    public function recordStatusChange($status, $by, $note=null, $data = null)
    {
        $log = Log::log('payment.update', $note, $this, $status, $by, $data);

        return $log;
    }

    public function getHistory()
    {
        $histories = $this->logs()->whereTag('payment.update')->get();

        return $histories;
    }

    /**
     * Create external reference for payment gateway. Ex: Order ID for Paypal or Midtrans
     * @return string
     */
    public function generateExternalReference()
    {
        return $this->invoice->reference.'/'.$this->id;
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
            'amount' => $order->getOutstandingAmount(),
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

        if(!empty($options['response'])){
            $payment->response = $options['response'];
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

    /**
     * Create Initiate Payment
     * @param Order $order
     * @return Payment
     */
    public static function createIniatePayment(Order $order)
    {
        $payment = self::createPayment($order, null, self::STATUS_INITIATE, $order->paymentMethod);

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
            self::STATUS_INITIATE => 'Initiate',
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }

    /**
     * Retrieve payment given external Order ID. Ex: From Paypal or Midtrans
     * @param string $orderId
     * @return self
     */
    public static function getPaymentFromExternal($orderId)
    {
        $explodedOrderId = explode('/', $orderId);
        $paymentId = array_pop($explodedOrderId);

        $payment = self::findOrFail($paymentId);

        return $payment;
    }
}
