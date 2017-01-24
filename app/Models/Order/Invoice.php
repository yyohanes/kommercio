<?php

namespace Kommercio\Models\Order;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Kommercio\Facades\ProjectHelper;

class Invoice extends Model
{
    const STATUS_UNPAID = 'unpaid';
    const STATUS_PAID = 'paid';
    const STATUS_VOID = 'void';

    protected $fillable = ['reference', 'total', 'status', 'payment_date', 'public_id'];
    protected $dates = ['payment_date'];

    private $_referenceFormat;

    public function __construct($attributes = [])
    {
        $this->_referenceFormat = ProjectHelper::getConfig('invoice_options.reference_format');

        parent::__construct($attributes);
    }

    //Methods
    public function generateReference()
    {
        $date = $this->created_at?:Carbon::now();
        $format = $this->_referenceFormat;
        $formatElements = explode(':', $format);

        $counterLength = ProjectHelper::getConfig('invoice_options.reference_counter_length');

        $lastInvoice = self::whereRaw("DATE_FORMAT(created_at, '%d-%m-%Y') = ?", [$date->format('d-m-Y')])
            ->where('store_id', $this->order->store_id)
            ->orderBy(DB::raw('CAST(counter as UNSIGNED)'), 'DESC')
            ->first();
        $totalInvoice = $lastInvoice?intval($lastInvoice->counter):0;
        $this->counter = str_pad($totalInvoice + 1, $counterLength, 0, STR_PAD_LEFT);

        $reference = '';
        foreach($formatElements as $formatElement){
            switch($formatElement){
                case 'store_code':
                    $store = $this->order->store;
                    if($store){
                        $reference .= $store->code;
                    }
                    break;
                case 'invoice_year':
                    $reference .= $date->format('y');
                    break;
                case 'invoice_month':
                    $reference .= $date->format('m');
                    break;
                case 'invoice_day':
                    $reference .= $date->format('d');
                    break;
                case 'counter':
                    $reference .= $this->counter;
                    break;
                default:
                    break;
            }
        }

        $this->reference = $reference;

        //Final duplicate order reference check
        while(self::where('reference', $reference)->count() > 0){
            $reference = $this->generateReference();
        }

        return $reference;
    }

    public function generatePublicId()
    {
        $uuid = ProjectHelper::generateUuid();

        $this->public_id = $uuid;

        while(self::where('public_id', $uuid)->count() > 0){
            $uuid = $this->generatePublicId();
        }

        return $uuid;
    }

    //Relations
    public function order()
    {
        return $this->belongsTo('Kommercio\Models\Order\Order');
    }

    public function store()
    {
        return $this->belongsTo('Kommercio\Models\Store');
    }

    public function payments()
    {
        return $this->hasMany('Kommercio\Models\Payment');
    }

    //Methods
    public function markAsPaid($payment_date = null)
    {
        $this->status = self::STATUS_PAID;
        $this->payment_date = $payment_date?$payment_date:Carbon::now();
        $this->save();
    }

    //Statics
    public static function createInvoice(Order $order, $date = null, $amount = null)
    {
        if(!$amount){
            $amount = $order->total;
        }

        $invoice = new self([
            'total' => $amount,
            'status' => self::STATUS_UNPAID
        ]);
        if($date){
            $invoice->setCreatedAt($date);
        }
        $invoice->order()->associate($order);
        $invoice->store()->associate($order->store);
        $invoice->generateReference();
        $invoice->generatePublicId();
        $invoice->save();

        return $invoice;
    }

    public static function getStatusOptions($process)
    {
        $array = [
            self::STATUS_UNPAID => 'Unpaid',
            self::STATUS_PAID => 'Paid',
            self::STATUS_VOID => 'Void',
        ];

        return $array[$process];
    }

    /**
     * @param $public_id Public ID of the invoice
     * @return self
     */
    public static function findPublic($public_id)
    {
        return self::where('public_id', $public_id)->first();
    }
}
