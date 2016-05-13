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
}
