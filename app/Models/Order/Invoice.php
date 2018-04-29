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

    public $fillable = ['reference', 'total', 'status', 'payment_date', 'due_date', 'public_id'];
    protected $dates = ['payment_date', 'due_date'];

    //Methods
    public function generateReference()
    {
        $date = $this->created_at?:Carbon::now();
        $format = ProjectHelper::getConfig('invoice_options.reference_format');
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

    /**
     * Invoice is considered overdue after due_date at 23:59:59
     *
     * @param Carbon|null $compareTime Date to check against
     * @return bool
     */
    public function isOverdue(Carbon $compareTime = null)
    {
        if ($this->due_date) {
            if (!$compareTime) {
                $compareTime = Carbon::today();
            }

            $compareTime->setTime(0, 0, 0);
            $this->due_date->setTime(23, 59, 59);

            return $compareTime->gt($this->due_date);
        }

        return FALSE;
    }

    /**
     * How many days until overdue
     *
     * @param Carbon|null $compareTime Date to check against
     * @return int
     */
    public function daysToOverdue(Carbon $compareTime = null)
    {
        if ($this->due_date) {
            if (!$compareTime) {
                $compareTime = Carbon::today();
            }

            $compareTime->setTime(0, 0, 0);
            $this->due_date->setTime(0, 0, 0);

            return $compareTime->diffInDays($this->due_date, false);
        }

        return 0;
    }

    // Scopes
    /**
     * Scope to select invoices by number of days to overdue
     *
     * @param $query
     * @param int $days number of days to overdue
     * @param Carbon|null $compareTime date to compare against due date
     * @param string $operator
     */
    public function scopeWhereDaysToOverdue($query, int $days, Carbon $compareTime = null, string $operator = '=')
    {
        if (!$compareTime) {
            $compareTime = Carbon::today();
        }

        $compareTime->setTime(0, 0, 0);

        $query->whereRaw('TIMESTAMPDIFF(DAY, ?, CONCAT(DATE_FORMAT(due_date, "%Y-%m-%d"), " 00:00:00")) ' . $operator . ' ?', [$compareTime, $days]);
    }

    //Statics
    public static function createInvoice(Order $order, $date = null, $amount = null, $dueDate = null)
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

        if ($dueDate && $dueDate instanceof Carbon) {
            $invoice->due_date = $dueDate;
        }

        $invoice->order()->associate($order);
        $invoice->store()->associate($order->store);
        $invoice->generateReference();
        $invoice->generatePublicId();
        $invoice->save();

        $order->load('invoices');

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
