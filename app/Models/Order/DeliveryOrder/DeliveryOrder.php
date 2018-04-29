<?php

namespace Kommercio\Models\Order\DeliveryOrder;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Kommercio\Events\DeliveryOrderEvent;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Customer;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;
use Kommercio\Models\Log;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Profile\Profile;
use Kommercio\Models\Store;
use Kommercio\Traits\Model\AuthorSignature;
use Kommercio\Traits\Model\HasDataColumn;

class DeliveryOrder extends Model implements AuthorSignatureInterface
{
    use HasDataColumn, AuthorSignature;

    const STATUS_PENDING = 'pending';
    const STATUS_PROGRESS = 'progress';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_CANCELLED = 'cancelled';

    public $fillable = ['reference', 'counter', 'total_quantity', 'total_weight', 'status', 'notes'];

    // Relations
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function items()
    {
        return $this->hasMany(DeliveryOrderItem::class)->orderBy('sort_order', 'ASC');
    }

    public function shippingProfile()
    {
        return $this->belongsTo(Profile::class, 'shipping_profile_id');
    }

    public function logs()
    {
        return $this->morphMany(Log::class, 'loggable');
    }

    // Accessors
    public function getIsCancellableAttribute()
    {
        return !in_array($this->status, [self::STATUS_CANCELLED, self::STATUS_SHIPPED]) && Gate::allows('access', ['cancel_delivery_order']);
    }

    public function getIsShippableAttribute()
    {
        return !in_array($this->status, [self::STATUS_CANCELLED, self::STATUS_SHIPPED]) && Gate::allows('access', ['complete_delivery_order']);
    }

    /**
     * Get lineItems related to Delivery Order items
     *
     * @return Collection
     */
    public function getLineItemsAttribute()
    {
        $return = collect([]);

        foreach ($this->items as $item) {
            $return->push($item->lineItem);
        }

        return $return;
    }

    // Methods
    public function getProfileOrNew($type = 'shipping')
    {
        if($type == 'billing'){
            $profileRelation = 'billingProfile';
            $profile = $this->billingProfile;
        }else{
            $profileRelation = 'shippingProfile';
            $profile = $this->shippingProfile;
        }

        if(!$profile){
            $profile = new Profile();
            $profile->profileable()->associate($this);
            $profile->save();

            $this->$profileRelation()->associate($profile);
            $this->save();
        }

        return $profile;
    }

    public function generateReference($last_number = null)
    {
        $format = ProjectHelper::getConfig('delivery_order_options.reference_format');
        $formatElements = explode(':', $format);

        $counterLength = ProjectHelper::getConfig('delivery_order_options.reference_counter_length');
        $now = Carbon::now();

        if($last_number){
            $totalDO = intval($last_number);
        }else{
            $lastDO = self::whereRaw("DATE_FORMAT(created_at, '%d-%m-%Y') = ?", [$now->format('d-m-Y')])
                ->where('store_id', $this->store_id)
                ->orderBy('counter', 'DESC')
                ->first();

            $totalDO = $lastDO?$lastDO->counter:0;
        }

        $this->counter = $totalDO + 1;

        $reference = '';
        foreach($formatElements as $formatElement){
            switch($formatElement){
                case 'store_code':
                    $store = $this->store;
                    if($store){
                        $reference .= $store->code;
                    }
                    break;
                case 'delivery_order_year':
                    $reference .= $now->format('y');
                    break;
                case 'delivery_order_month':
                    $reference .= $now->format('m');
                    break;
                case 'delivery_order_day':
                    $reference .= $now->format('d');
                    break;
                case 'counter':
                    $reference .= str_pad($totalDO + 1, $counterLength, 0, STR_PAD_LEFT);
                    break;
                default:
                    break;
            }
        }

        $this->reference = $reference;

        //Final duplicate order reference check
        while(self::where('reference', $reference)->count() > 0){
            $reference = $this->generateReference($this->counter);
        }

        return $reference;
    }

    public function calculateTotalQuantity()
    {
        $quantity = 0;

        foreach($this->items as $item){
            $quantity += abs($item->quantity);
        }

        $quantity = $quantity?:0;

        $this->total_quantity = $quantity;

        return $this->total_quantity;
    }

    public function calculateTotalWeight()
    {
        $weight = 0;

        foreach($this->items as $item){
            $weight += abs($item->weight * $item->quantity);
        }

        $weight = $weight?:1000;

        $this->total_weight = $weight;

        return $this->total_weight;
    }

    public function changeStatus($status, $notify = false, $note=null, $by = null, $data = null)
    {
        $oldStatus = $this->status;
        $this->status = $status;
        $this->save();

        if($oldStatus != $status){
            if($status == static::STATUS_SHIPPED){
                Event::fire(new DeliveryOrderEvent(DeliveryOrderEvent::ON_SHIPPED_DELIVERY_ORDER, $this, [
                    'send_notification' => $notify,
                ]));
            }elseif($status == static::STATUS_CANCELLED){
                Event::fire(new DeliveryOrderEvent(DeliveryOrderEvent::ON_CANCELLED_DELIVERY_ORDER, $this, [
                    'send_notification' => $notify,
                ]));
            }
        }

        if(!$by){
            $by = Auth::user()->email;
        }
        $this->recordStatusChange($status, $by, $note, $data);
    }

    public function recordStatusChange($status, $by, $note=null, $data = null)
    {
        $log = Log::log('delivery_order.update', $note, $this, $status, $by, $data);

        return $log;
    }

    public function getHistory()
    {
        $histories = $this->logs()->whereTag('delivery_order.update')->get();

        return $histories;
    }

    // Static
    public static function getStatusOptions($option=null)
    {
        $array = [
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PROGRESS => 'Progress',
            self::STATUS_SHIPPED => 'Shipped',
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }

    /**
     * Get Delivery Order status that should be counted
     *
     * @return array
     */
    public static function getCountedStatus()
    {
        return [
            self::STATUS_SHIPPED,
            self::STATUS_PROGRESS,
            self::STATUS_PENDING,
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::deleted(function($model){
            if($model->shippingProfile){
                $model->shippingProfile->delete();
            }
        });
    }
}
