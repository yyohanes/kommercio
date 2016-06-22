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
        $fullName = $this->getProfile()->full_name;

        if(empty(trim($fullName))){
            $fullName = '';
        }

        return $fullName;
    }

    public function getRoleAttribute()
    {
        return $this->roles->first();
    }

    public function getIsMasterSuperAdminAttribute()
    {
        return $this->id == 1;
    }

    public function getIsSuperAdminAttribute()
    {
        return $this->id == 1 || ($this->role && $this->role->id == 1);
    }

    //Scopes
    public function scopeNotCustomer($query)
    {
        $query->whereDoesntHave('customer');
    }

    //Relations
    public function roles()
    {
        return $this->belongsToMany('Kommercio\Models\Role\Role');
    }

    public function stores()
    {
        return $this->belongsToMany('Kommercio\Models\Store');
    }

    public function customer()
    {
        return $this->hasOne('Kommercio\Models\Customer');
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
