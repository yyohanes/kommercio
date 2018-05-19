<?php

namespace Kommercio\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Laravel\Passport\HasApiTokens;
use Kommercio\Models\Interfaces\CacheableInterface;
use Kommercio\Notifications\Auth\ResetPasswordNotification;
use Kommercio\Traits\Model\Profileable;

class User extends Authenticatable implements CacheableInterface
{
    use Profileable, Notifiable, HasApiTokens;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_BLOCKED = 'blocked';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = [
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

    public function sendPasswordResetNotification($token)
    {
        $redirectTo = null;

        // TODO: Find another way to get `redirectTo` instead of relying on Request facade
        if (Request::filled('redirectTo')) {
            $redirectTo = Request::input('redirectTo');
        }

        $this->notify(new ResetPasswordNotification($this, $token, $redirectTo));
    }

    public function getCacheKeys()
    {
        return [
            $this->getTable() . '_' . $this->id,
        ];
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
        return $this->hasOne(Customer::class);
    }

    public function socialAccounts()
    {
        return $this->hasMany(SocialAccount::class);
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

    public static function findById(int $id) {
        $tableName = (new static)->getTable();

        return Cache::remember($tableName . '_' . $id, 3600, function() use ($id) {
            return static::where('id', $id)->first();
        });
    }
}
