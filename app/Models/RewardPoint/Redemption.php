<?php

namespace Kommercio\Models\RewardPoint;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Customer;
use Kommercio\Models\PriceRule\Coupon;

class Redemption extends Model
{
    const STATUS_UNUSED = 'unused';
    const STATUS_USED = 'used';

    protected $fillable = ['points', 'status'];

    //Relations
    public function reward()
    {
        return $this->belongsTo('Kommercio\Models\RewardPoint\Reward');
    }

    public function customer()
    {
        return $this->belongsTo('Kommercio\Models\Customer');
    }

    public function coupon()
    {
        return $this->belongsTo('Kommercio\Models\PriceRule\Coupon');
    }

    //Statics
    public static function redeem(Customer $customer, Reward $reward)
    {
        $rewardObject = $reward->generateReward($customer);

        $redemption = new self([
            'points' => $reward->points,
            'status' => self::STATUS_UNUSED
        ]);

        $redemption->reward()->associate($reward);
        $redemption->customer()->associate($customer);

        if($rewardObject instanceof Coupon){
            $redemption->coupon()->associate($rewardObject);
        }

        $redemption->save();

        $customer->deductRewardPoint($reward->points, [
            'reason' => 'Reward '.$reward->name.' redemption',
            'status' => RewardPointTransaction::STATUS_APPROVED
        ]);

        return $redemption;
    }

    public static function getStatusOptions($option=null)
    {
        $array = [
            self::STATUS_USED => 'Used',
            self::STATUS_UNUSED => 'Unused',
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }
}
