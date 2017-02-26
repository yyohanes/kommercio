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

    private $_manage_all_stores;
    private $_manage_multiple_stores;
    private $_managed_stores;

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    //Methods
    public function getManagedStores()
    {
        if(!isset($this->_managed_stores)){
            if($this->isSuperAdmin){
                $this->_managed_stores = Store::orderBy('created_at', 'DESC')->get();
            }else{
                $this->_managed_stores = $this->stores;
            }
        }

        return $this->_managed_stores;
    }

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

    public function getIsCustomerAttribute()
    {
        $customer = $this->customer;

        return !empty($customer);
    }

    public function getIsMasterSuperAdminAttribute()
    {
        return $this->id == 1;
    }

    public function getIsSuperAdminAttribute()
    {
        return $this->id == 1 || ($this->role && $this->role->id == 1);
    }

    public function getManageMultipleStoresAttribute()
    {
        if(!isset($this->_manage_multiple_stores)){
            $this->_manage_multiple_stores = $this->getManagedStores()->count() > 1;
        }

        return $this->_manage_multiple_stores;
    }

    public function getManageAllStoresAttribute()
    {
        if(!isset($this->_manage_all_stores)){
            $storeCount = Store::count();
            $this->_manage_all_stores = $this->getManagedStores()->count() >= $storeCount;
        }

        return $this->_manage_all_stores;
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
        return $this->belongsToMany('Kommercio\Models\Store')->orderBy('created_at', 'DESC');
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
