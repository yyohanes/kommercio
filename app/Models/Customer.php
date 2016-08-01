<?php

namespace Kommercio\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Kommercio\Models\Order\Order;
use Kommercio\Traits\Model\Profileable;

class Customer extends Model
{
    use Profileable;

    const SALUTE_MR = 'mr';
    const SALUTE_MRS = 'mrs';
    const SALUTE_MS = 'ms';

    protected $guarded = ['profile', 'user'];
    protected $casts = [
        'is_guest' => 'boolean',
        'is_virgin' => 'boolean'
    ];
    protected $dates = ['last_active'];
    protected $profileKeys = ['profile', 'shippingProfile'];

    //Relations
    public function user()
    {
        return $this->belongsTo('Kommercio\Models\User');
    }

    public function orders()
    {
        return $this->hasMany('Kommercio\Models\Order\Order')->orderBy('checkout_at', 'DESC');
    }

    public function shippingProfile()
    {
        return $this->belongsTo('Kommercio\Models\Profile\Profile', 'shipping_profile_id');
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
            $status = [Order::STATUS_COMPLETED, Order::STATUS_PROCESSING, Order::STATUS_PENDING];
        }

        $customerTable = $this->orders()->getParent()->getTable();

        $orderQuery = Order::selectRaw('customer_id, SUM(total * conversion_rate) AS total, SUM(discount_total * conversion_rate) AS discount_total, SUM(shipping_total * conversion_rate) AS shipping_total, SUM(tax_total * conversion_rate) AS tax_total, SUM(additional_total * conversion_rate) AS additional_total')
            ->groupBy('customer_id')
            ->whereIn('status', $status);

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

    public static function getByEmail($email)
    {
        $qb = self::whereField('email', $email);

        return $qb->first();
    }
}
