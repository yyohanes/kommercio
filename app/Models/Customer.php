<?php

namespace Kommercio\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Kommercio\Models\Order\Order;
use Kommercio\Models\RewardPoint\RewardPointTransaction;
use Kommercio\Traits\Model\Profileable;

class Customer extends Model
{
    use Profileable;

    const SALUTE_MR = 'mr';
    const SALUTE_MRS = 'mrs';
    const SALUTE_MS = 'ms';

    const PROFILE_NAME_HOME = 'home';
    const PROFILE_NAME_OFFICE = 'office';

    protected $guarded = ['profile', 'user'];
    protected $casts = [
        'is_guest' => 'boolean',
        'is_virgin' => 'boolean'
    ];
    protected $dates = ['last_active'];
    protected $profileKeys = ['profile'];

    //Relations
    public function user()
    {
        return $this->belongsTo('Kommercio\Models\User');
    }

    public function orders()
    {
        return $this->hasMany('Kommercio\Models\Order\Order')->orderBy('checkout_at', 'DESC')->checkout();
    }

    public function profiles()
    {
        return $this->morphMany('Kommercio\Models\Profile\Profile', 'profileable');
    }

    public function savedProfiles()
    {
        return $this->belongsToMany('Kommercio\Models\Profile\Profile', 'customer_profile')->withPivot(['name', 'billing', 'shipping']);
    }

    public function rewardPointTransactions()
    {
        return $this->hasMany('Kommercio\Models\RewardPoint\RewardPointTransaction')->orderBy('created_at', 'DESC');
    }

    public function coupons()
    {
        return $this->hasMany('Kommercio\Models\PriceRule\Coupon');
    }

    //Methods
    public function saveAddress($data, $billing = false, $shipping = false)
    {
        $profile = new Profile\Profile();

        $this->savedProfiles()->save($profile, [
            'name' => isset($data['name'])?$data['name']:'',
            'billing' => $billing,
            'shipping' => $shipping
        ]);

        $profile->saveDetails($data['profile']);

        return $profile;
    }

    public function getDefaultProfiles()
    {
        return $this->savedProfiles()->wherePivot('billing', 1)->orWherePivot('shipping', 1)->get();
    }

    public function addRewardPoint($amount, $data, $order = null)
    {
        return $this->newRewardPoint($amount, RewardPointTransaction::TYPE_ADD, $data, $order);
    }

    public function deductRewardPoint($amount, $data, $order = null)
    {
        return $this->newRewardPoint($amount, RewardPointTransaction::TYPE_DEDUCT, $data, $order);
    }

    protected function newRewardPoint($amount, $type, $data, $order)
    {
        $data = array_merge($data, [
            'amount' => $amount,
            'type' => $type,
            'status' => isset($data['status'])?$data['status']:RewardPointTransaction::STATUS_REVIEW
        ]);

        $rewardPointTransaction = new RewardPointTransaction($data);

        $rewardPointTransaction->customer()->associate($this);
        if($order){
            $rewardPointTransaction->order()->associate($order);
        }

        $rewardPointTransaction->save();

        if($rewardPointTransaction->status == RewardPointTransaction::STATUS_APPROVED){
            if($rewardPointTransaction->type == RewardPointTransaction::TYPE_ADD){
                $this->increment('reward_points', $rewardPointTransaction->amount);
            }elseif($rewardPointTransaction->type == RewardPointTransaction::TYPE_DEDUCT){
                $this->decrement('reward_points', $rewardPointTransaction->amount);
            }
        }

        return $rewardPointTransaction;
    }

    //Scopes
    public function scopeWhereUserStatus($query, $status)
    {
        $query->whereHas('user', function($qb) use ($status){
            $qb->where('status', $status);
        });
    }

    public function scopeJoinOrderTotal($query, $status = [])
    {
        if(empty($status)){
            $status = ['\''.Order::STATUS_COMPLETED.'\'', '\''.Order::STATUS_PROCESSING.'\'', '\''.Order::STATUS_PENDING.'\''];
        }

        $customerTable = $this->orders()->getParent()->getTable();

        $orderQuery = Order::selectRaw('customer_id, SUM(total * conversion_rate) AS total, SUM(discount_total * conversion_rate) AS discount_total, SUM(shipping_total * conversion_rate) AS shipping_total, SUM(tax_total * conversion_rate) AS tax_total, SUM(additional_total * conversion_rate) AS additional_total')
            ->groupBy('customer_id')
            ->whereRaw('status IN ('.implode(',',$status).')');

        $query
            ->selectRaw($customerTable.'.*, O.*')
            ->leftJoin(DB::raw('('.$orderQuery->toSql().') AS O'), 'O.customer_id', '=', $customerTable.'.id')
            ->mergeBindings($orderQuery->getQuery());
    }

    //Accessors
    public function getFullNameAttribute()
    {
        return $this->getProfile()->full_name;
    }

    public function getDefaultBillingProfileAttribute()
    {
        foreach($this->savedProfiles as $savedProfile){
            if($savedProfile->pivot->billing){
                return $savedProfile;
                break;
            }
        }

        return null;
    }

    public function getDefaultShippingProfileAttribute()
    {
        foreach($this->savedProfiles as $savedProfile){
            if($savedProfile->pivot->shipping){
                return $savedProfile;
                break;
            }
        }

        return null;
    }

    //Statics
    public static function saveCustomer($profileData=null, $accountData=null, $touchAccount = TRUE, $newRegistration = FALSE)
    {
        if(!isset($profileData['email']) || !filter_var($profileData['email'], FILTER_VALIDATE_EMAIL)){
            throw new \Exception('Email is required when saving customer.');
        }

        $customer = self::whereField('email', $profileData['email'])->first();

        if($newRegistration && $customer){
            $customer->is_virgin = TRUE;
        }

        if(!$customer){
            $customer = new self();
            $customer->is_virgin = TRUE;
        }

        if(!empty($accountData)){
            if(!isset($accountData['email']) || !filter_var($accountData['email'], FILTER_VALIDATE_EMAIL)){
                throw new \Exception('Email is required when creating new account.');
            }

            $user = $customer->user;

            if(!$user){
                $user = new User();
            }

            $user->fill([
                'email' => $accountData['email'],
                'status' => isset($accountData['status'])?$accountData['status']:User::STATUS_ACTIVE
            ]);

            if(isset($accountData['password'])){
                $user->password = bcrypt($accountData['password']);
            }

            $user->save();

            $customer->user()->associate($user);
        }else{
            if($touchAccount){
                if($customer->user){
                    $customer->user->delete();
                }
            }
        }

        $customer->save();

        if($customer->is_virgin){
            $customer->saveProfile($profileData);
        }

        return $customer;
    }

    public static function getSaluteOptions($option=null)
    {
        $array = [
            self::SALUTE_MR => trans('Mr'),
            self::SALUTE_MRS => trans('Mrs'),
            self::SALUTE_MS => trans('Ms'),
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }

    public static function getProfileNameOptions($option=null)
    {
        $array = [
            self::PROFILE_NAME_HOME => trans('Home'),
            self::PROFILE_NAME_OFFICE => trans('Office'),
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }

    public static function getByEmail($email)
    {
        $qb = self::whereField('email', $email);

        return $qb->first();
    }
}
