<?php

namespace Kommercio\Models;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Traits\Model\Profileable;

class Customer extends Model
{
    use Profileable;

    const SALUTE_MR = 'mr';
    const SALUTE_MRS = 'mrs';
    const SALUTE_MS = 'ms';

    protected $guarded = ['profile', 'user'];
    protected $casts = [
        'is_guest' => 'boolean'
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
        return $this->hasMany('Kommercio\Models\Order\Order');
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

    //Accessors
    public function getFullNameAttribute()
    {
        return $this->getProfile()->full_name;
    }

    //Statics
    public static function saveCustomer($profileData=null, $accountData=null, $touchAccount = TRUE)
    {
        if(!isset($profileData['email']) || !filter_var($profileData['email'], FILTER_VALIDATE_EMAIL)){
            throw new \Exception('Email is required when saving customer.');
        }

        $customer = self::whereField('email', $profileData['email'])->first();
        if(!$customer){
            $customer = new self();
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

        $customer->saveProfile($profileData);

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
