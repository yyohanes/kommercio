<?php

namespace Kommercio\Models\RewardPoint;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Customer;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;
use Kommercio\Models\Interfaces\StoreManagedInterface;
use Kommercio\Models\PriceRule\Coupon;
use Kommercio\Models\User;
use Kommercio\Traits\Model\AuthorSignature;
use Kommercio\Traits\Model\HasDataColumn;
use Kommercio\Traits\Model\ToggleDate;

class Reward extends Model implements StoreManagedInterface
{
    use HasDataColumn, Translatable, ToggleDate {
        Translatable::setAttribute as translateableSetAttribute;
        ToggleDate::setAttribute insteadof Translatable;
    }

    const TYPE_ONLINE_COUPON = 'online_coupon';
    const TYPE_OFFLINE_COUPON = 'offline_coupon';

    public $translatedAttributes = ['name', 'description', 'images'];
    public $fillable = ['name', 'description', 'type', 'points', 'active', 'data'];
    protected $toggleFields = ['active'];
    protected $casts = [
        'active' => 'boolean',
    ];

    //Methods
    public function checkStorePermissionByUser(User $user)
    {
        if($user->manageAllStores){
            return true;
        }

        return $this->store_id && in_array($this->store_id, $user->getManagedStores()->pluck('id')->all());
    }

    public function generateReward(Customer $customer, Redemption $redemption = null)
    {
        if(in_array($this->type, [self::TYPE_OFFLINE_COUPON, self::TYPE_ONLINE_COUPON])){
            $coupon = $this->constructCouponByType($this->type);

            if($redemption){
                $coupon->redemption()->associate($redemption);
            }

            $coupon->customer()->associate($customer);
            $coupon->generateCode();
            $coupon->save();

            return $coupon;
        }
    }

    protected function constructCouponByType($type)
    {
        switch($type){
            case self::TYPE_OFFLINE_COUPON:
                $coupon = new Coupon([
                    'type' => Coupon::TYPE_OFFLINE,
                    'max_usage' => 1
                ]);
                break;
            default:
                $coupon = new Coupon([
                    'type' => Coupon::TYPE_ONLINE,
                    'max_usage' => 1
                ]);
                $coupon->cartPriceRule()->associate($this->cartPriceRule);
                break;
        }

        return $coupon;
    }

    //Scopes
    public function scopeActive($query)
    {
        $query->where('active', 1);
    }

    //Relations
    public function store()
    {
        return $this->belongsTo('Kommercio\Models\Store');
    }

    public function cartPriceRule()
    {
        return $this->belongsTo('Kommercio\Models\PriceRule\CartPriceRule');
    }

    //Statics
    public static function getTypeOptions($option=null)
    {
        $array = [
            self::TYPE_ONLINE_COUPON => 'Online Coupon',
            self::TYPE_OFFLINE_COUPON => 'Offline Coupon',
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }
}
