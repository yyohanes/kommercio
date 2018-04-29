<?php

namespace Kommercio\Models\PriceRule;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;
use Kommercio\Models\Interfaces\RewardObjectInterface;
use Kommercio\Models\Log;
use Kommercio\Models\Order\Order;
use Kommercio\Traits\Model\AuthorSignature;
use Kommercio\Traits\Model\HasDataColumn;

class Coupon extends Model implements AuthorSignatureInterface, RewardObjectInterface
{
    use AuthorSignature, HasDataColumn;

    const TYPE_ONLINE = 'online';
    const TYPE_OFFLINE = 'offline';

    public $fillable = ['coupon_code', 'type', 'max_usage'];

    //Methods
    public function getUsage()
    {
        return $this->cartPriceRule->getUsageByCoupon($this);
    }

    public function getRewardUsageCount()
    {
        return $this->rewardUsageLogs->count();
    }

    public function markRewardUsed()
    {
        Log::log('redemption.used', 'Coupon is mark as used', $this);
    }

    //Get cart price rule and tie it with specific Coupon
    public function getCartPriceRule()
    {
        $this->cartPriceRule->coupon = $this;

        return $this->cartPriceRule;
    }

    public function generateCode($length = 6)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        while(self::where('coupon_code', $randomString)->count() > 0){
            $randomString = $this->generateCode($length);
        }

        $this->coupon_code = $randomString;

        return $randomString;
    }

    public function validateCustomer($customer)
    {
        $valid = empty($this->customer);

        if(!empty($this->customer) && !empty($customer)){
            $valid = $customer->id == $this->customer->id;
        }

        return $valid;
    }

    //Relations
    public function cartPriceRule()
    {
        return $this->belongsTo('Kommercio\Models\PriceRule\CartPriceRule');
    }

    public function customer()
    {
        return $this->belongsTo('Kommercio\Models\Customer');
    }

    public function redemption()
    {
        return $this->hasOne('Kommercio\Models\RewardPoint\Redemption');
    }

    public function rewardUsageLogs()
    {
        return $this->morphMany('Kommercio\Models\Log', 'loggable')->whereTag('redemption.used');
    }

    //Statics
    public static function getCouponByCode($coupon_code)
    {
        $qb = self::where('coupon_code', $coupon_code)->where('type', Coupon::TYPE_ONLINE);

        return $qb->first();
    }

    public static function getTypeOptions($option=null)
    {
        $array = [
            self::TYPE_ONLINE => 'Online',
            self::TYPE_OFFLINE => 'Offline',
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }
}
