<?php

namespace Kommercio\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;
use Kommercio\Models\Profile\Profile;
use Kommercio\Models\Tax;
use Kommercio\Traits\Model\AuthorSignature;

class Order extends Model implements AuthorSignatureInterface
{
    use SoftDeletes, AuthorSignature;

    const STATUS_CANCELLED = 'cancelled';
    const STATUS_ADMIN_CART = 'admin_cart';
    const STATUS_CART = 'cart';
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';

    protected $guarded = [];
    protected $dates = ['deleted_at', 'delivery_date', 'checkout_at'];

    //Relations
    public function lineItems()
    {
        return $this->hasMany('Kommercio\Models\Order\LineItem')->orderBy('sort_order', 'ASC');
    }

    public function customer()
    {
        return $this->belongsTo('Kommercio\Models\Customer');
    }

    public function store()
    {
        return $this->belongsTo('Kommercio\Models\Store');
    }

    public function billingProfile()
    {
        return $this->belongsTo('Kommercio\Models\Profile\Profile', 'billing_profile_id');
    }

    public function shippingProfile()
    {
        return $this->belongsTo('Kommercio\Models\Profile\Profile', 'shipping_profile_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo('Kommercio\Models\PaymentMethod\PaymentMethod');
    }

    public function shippingMethod()
    {
        return $this->belongsTo('Kommercio\Models\ShippingMethod\ShippingMethod');
    }

    public function payments()
    {
        return $this->hasMany('Kommercio\Models\Order\Payment');
    }

    //Methods
    public function generateReference()
    {
        $format = config('kommercio.order_number_format');
        $formatElements = explode(':', $format);

        $counterLength = config('kommercio.order_number_counter_length');

        $lastOrder = self::checkout()
            ->whereRaw("DATE_FORMAT(checkout_at, '%m-%Y') = ?", [$this->checkout_at->format('m-Y')])
            ->orderBy(DB::raw('CAST(order_number as UNSIGNED)'), 'DESC')
            ->first();
        $totalCheckedOutOrder = $lastOrder?intval($lastOrder->order_number):0;
        $this->order_number = str_pad($totalCheckedOutOrder + 1, $counterLength, 0, STR_PAD_LEFT);

        $orderReference = '';
        foreach($formatElements as $formatElement){
            switch($formatElement){
                case 'store_code':
                    $store = $this->store;
                    if($store){
                        $orderReference .= $store->code;
                    }
                    break;
                case 'order_year':
                    $orderReference .= $this->checkout_at->format('Y');
                    break;
                case 'order_month':
                    $orderReference .= $this->checkout_at->format('m');
                    break;
                case 'order_day':
                    $orderReference .= $this->checkout_at->format('d');
                    break;
                case 'counter':
                    $orderReference .= $this->order_number;
                    break;
                default:
                    break;
            }
        }

        $this->reference = $orderReference;
        return $orderReference;
    }

    public function processStocks()
    {
        foreach($this->lineItems as $lineItem){
            if($lineItem->isProduct){
                $lineItem->product->reduceStock($lineItem->quantity);
            }
        }
    }

    public function returnStocks()
    {
        foreach($this->lineItems as $lineItem){
            if($lineItem->isProduct){
                $lineItem->product->increaseStock($lineItem->quantity);
            }
        }
    }

    public function getTaxes()
    {
        $qb = Tax::orderBy('sort_order', 'ASC')->active();

        $profile = $this->billingProfile->fillDetails();

        $taxes = Tax::getTaxes([
            'country_id' => $profile->country_id,
            'state_id' => $profile->state_id,
            'city_id' => $profile->city_id,
            'district_id' => $profile->district_id,
            'area_id' => $profile->area_id,
        ]);

        return $taxes;
    }

    public function saveProfile($type, $data)
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
            $this->load($profileRelation);
        }

        $profile->saveDetails($data);
    }

    public function calculateSubtotal()
    {
        $this->subtotal = 0;

        foreach($this->lineItems as $lineItem){
            if($lineItem->isProduct){
                $this->subtotal += $lineItem->calculateSubtotal();
            }
        }

        return $this->subtotal;
    }

    public function calculateShippingTotal()
    {
        $this->shipping_total = 0;

        foreach($this->lineItems as $lineItem){
            if($lineItem->isShipping){
                $this->shipping_total += $lineItem->calculateTotal();
            }
        }

        return $this->shipping_total;
    }

    public function calculateDiscountTotal()
    {
        $this->discount_total = $this->calculateProductTotal() - $this->calculateSubtotal();

        return $this->discount_total;
    }

    public function calculateTaxTotal()
    {
        $this->tax_total = 0;

        return $this->tax_total;
    }

    public function calculateAdditionalTotal()
    {
        $this->additional_total = 0;

        foreach($this->lineItems as $lineItem){
            if($lineItem->isFee){
                $this->additional_total += $lineItem->calculateTotal();
            }
        }

        return $this->additional_total;
    }

    public function calculateTotal()
    {
        $subtotal = $this->calculateSubtotal();
        $shippingTotal = $this->calculateShippingTotal();
        $discountTotal = $this->calculateDiscountTotal();
        $additionalTotal = $this->calculateAdditionalTotal();
        $taxTotal = $this->calculateTaxTotal();

        $this->total = $subtotal + $shippingTotal + $discountTotal + $additionalTotal + $taxTotal;
    }

    public function calculateProductTotal()
    {
        $productTotal = 0;

        foreach($this->lineItems as $lineItem){
            if($lineItem->isProduct){
                $productTotal += $lineItem->calculateTotal();
            }
        }

        return $productTotal;
    }

    public function calculateQuantityTotal()
    {
        $quantityTotal = 0;

        foreach($this->lineItems as $lineItem){
            if($lineItem->isProduct){
                $quantityTotal += $lineItem->quantity;
            }
        }

        return $quantityTotal;
    }

    public function getPaidAmount()
    {
        $paidAmount = 0;

        foreach($this->payments as $payment){
            if($payment->isSuccess){
                $paidAmount += CurrencyHelper::convert($payment->amount, $payment->currency, $this->currency, $this->conversion_rate);
            }
        }

        return $paidAmount;
    }

    public function getOutstandingAmount()
    {
        $paidAmount = $this->getPaidAmount();

        return abs($this->total - $paidAmount);
    }

    //Scopes
    public function scopeJoinBillingProfile($query)
    {
        $profileDetailQuery = with(new Profile())->details();

        $query->leftJoin($profileDetailQuery->getRelated()->getTable().' AS BFNAME', function($join) use ($profileDetailQuery){
            $join->on('BFNAME.'.$profileDetailQuery->getPlainForeignKey(), '=', $this->getTable().'.'.$this->billingProfile()->getForeignKey())
                ->where('BFNAME.identifier', '=', 'first_name');
        });

        $query->leftJoin($profileDetailQuery->getRelated()->getTable().' AS BLNAME', function($join) use ($profileDetailQuery){
            $join->on('BLNAME.'.$profileDetailQuery->getPlainForeignKey(), '=', $this->getTable().'.'.$this->billingProfile()->getForeignKey())
                ->where('BLNAME.identifier', '=', 'last_name');
        });

        $query->addSelect(DB::raw($this->getTable().'.*, CONCAT_WS(" ", BFNAME.value, BLNAME.value) AS billing_full_name'));
    }

    public function scopeJoinShippingProfile($query)
    {
        $profileDetailQuery = with(new Profile())->details();

        $query->leftJoin($profileDetailQuery->getRelated()->getTable().' AS SFNAME', function($join) use ($profileDetailQuery){
            $join->on('SFNAME.'.$profileDetailQuery->getPlainForeignKey(), '=', $this->getTable().'.'.$this->shippingProfile()->getForeignKey())
                ->where('SFNAME.identifier', '=', 'first_name');
        });

        $query->leftJoin($profileDetailQuery->getRelated()->getTable().' AS SLNAME', function($join) use ($profileDetailQuery){
            $join->on('SLNAME.'.$profileDetailQuery->getPlainForeignKey(), '=', $this->getTable().'.'.$this->shippingProfile()->getForeignKey())
                ->where('SLNAME.identifier', '=', 'last_name');
        });

        $query->addSelect(DB::raw($this->getTable().'.*, CONCAT_WS(" ", SFNAME.value, SLNAME.value) AS shipping_full_name'));
    }

    public function scopeCheckout($query)
    {
        $query->whereNotIn('status', [self::STATUS_CART, self::STATUS_ADMIN_CART]);
    }

    //Accessors
    public function getIsEditableAttribute()
    {
        return in_array($this->status, [self::STATUS_ADMIN_CART, self::STATUS_CART, self::STATUS_PENDING]);
    }

    public function getIsDeleteableAttribute()
    {
        return in_array($this->status, [self::STATUS_ADMIN_CART, self::STATUS_CART]);
    }

    //Static
    public static function getStatusOptions($option=null, $all=false)
    {
        $array = [
            self::STATUS_CART => 'Cart',
            self::STATUS_ADMIN_CART => 'Admin Cart',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_COMPLETED => 'Completed',
        ];

        if(!$all){
            unset($array[self::STATUS_CART]);
            unset($array[self::STATUS_ADMIN_CART]);
        }

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }

    protected static function boot()
    {
        parent::boot();

        static::deleted(function($model){
            if($model->forceDeleting){
                if($model->billingProfile){
                    $model->billingProfile->delete();
                }

                if($model->shippingProfile){
                    $model->shippingProfile->delete();
                }
            }
        });
    }
}
