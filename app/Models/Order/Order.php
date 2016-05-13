<?php

namespace Kommercio\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;
use Kommercio\Models\Profile\Profile;
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
        return $this->hasMany('Kommercio\Models\Order\LineItem');
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

        $query->selectRaw($this->getTable().'.*, CONCAT(BFNAME.value, " ", BLNAME.value) AS billing_full_name');
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

        $query->selectRaw($this->getTable().'.*, CONCAT(SFNAME.value, " ", SLNAME.value) AS shipping_full_name');
    }

    //Static
    public static function getStatusOptions($option=null, $all=false)
    {
        $array = [
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_COMPLETED => 'Completed',
        ];

        if($all){
            $array = [self::STATUS_ADMIN_CART => 'Admin Cart', self::STATUS_CART => 'Cart'] + $array;
        }

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }
}
