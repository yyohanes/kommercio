<?php

namespace Kommercio\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Kommercio\Traits\Model\Profileable;

class User extends Authenticatable
{
    use Profileable;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_BLOCKED = 'blocked';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'password', 'status'
    ];

    protected $profileKeys = ['profile'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    //Accessors
    public function getFullNameAttribute()
    {
        return $this->getProfile()->full_name;
    }

    //Statics
    public static function getStatusOptions($option=null)
    {
        $array = [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_BLOCKED => 'Blocked',
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }
}
