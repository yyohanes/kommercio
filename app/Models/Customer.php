<?php

namespace Kommercio\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Kommercio\Facades\LanguageHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Facades\RuntimeCache;
use Kommercio\Models\Interfaces\CacheableInterface;
use Kommercio\Models\Order\Order;
use Kommercio\Models\RewardPoint\RewardPointTransaction;
use Kommercio\Traits\Model\Profileable;
use Kommercio\Traits\Model\FlatIndexable;

class Customer extends Model implements CacheableInterface
{
    use Profileable, FlatIndexable;

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

    protected $flatTable = 'customers_index';
    // TODO: Add more keys to flat index
    protected $flatIndexables = ['profile.email'];

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

    public function customerGroups()
    {
        return $this->belongsToMany('Kommercio\Models\Customer\CustomerGroup')->orderBy('sort_order', 'ASC')->withTimestamps();
    }

    public function redemptions()
    {
        return $this->hasMany('Kommercio\Models\RewardPoint\Redemption')->orderBy('created_at', 'DESC');
    }

    public function bookmarks()
    {
        return $this->hasMany('Kommercio\Models\Customer\Bookmark');
    }

    // Methods
    /**
     * @inheritdoc
     */
    public function getCacheKeys()
    {
        $tableName = $this->getTable();
        $keys = [
            $tableName . '_' . $this->id,
        ];

        return $keys;
    }

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

    public function generateReference() {
        if (!empty(trim($this->reference))) {
            return trim($this->reference);
        }

        $prefix = ProjectHelper::getConfig('customer.reference_prefix');
        $id = str_pad($this->getKey(), 6, '0', STR_PAD_LEFT);

        return sprintf('%s%s', empty($prefix) ? '' : $prefix . ' ', $id);
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
            $status = ['\''.Order::STATUS_COMPLETED.'\'', '\''.Order::STATUS_PROCESSING.'\'', '\''.Order::STATUS_PENDING.'\'', '\''.Order::STATUS_SHIPPED.'\''];
        }

        $customerTable = $this->orders()->getParent()->getTable();

        $orderQuery = Order::selectRaw('customer_id, COUNT(customer_id) AS total_orders, SUM(total * conversion_rate) AS total, SUM(discount_total * conversion_rate) AS discount_total, SUM(shipping_total * conversion_rate) AS shipping_total, SUM(tax_total * conversion_rate) AS tax_total, SUM(additional_total * conversion_rate) AS additional_total')
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
    public static function findById(int $id) {
        $tableName = (new static)->getTable();

        return Cache::remember($tableName . '_' . $id, 3600, function() use ($id) {
            return static::where('id', $id)->first();
        });
    }

    public static function saveCustomer($customer = null, $profileData=null, $accountData=null, $touchAccount = TRUE, $newRegistration = FALSE)
    {
        if(!isset($profileData['email']) || !filter_var($profileData['email'], FILTER_VALIDATE_EMAIL)){
            throw new \Exception('Email is required when saving customer.');
        }

        $customer = $customer ? : self::getByEmail($profileData['email']);

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

        if($customer->is_virgin){
            // Customer needs to be saved before so profile knows its owner
            $customer->save();
            $customer->saveProfile($profileData);
        }

        // Customer re-save to dispatch model `saved` event with updated profiles
        $customer->save();

        return $customer;
    }

    public static function getSaluteOptions($option=null)
    {
        $array = [
            self::SALUTE_MR => trans(LanguageHelper::getTranslationKey('interface.salute.mr')),
            self::SALUTE_MRS => trans(LanguageHelper::getTranslationKey('interface.salute.mrs')),
            self::SALUTE_MS => trans(LanguageHelper::getTranslationKey('interface.salute.ms')),
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }

    public static function getProfileNameOptions($option=null)
    {
        $array = [
            self::PROFILE_NAME_HOME => trans(LanguageHelper::getTranslationKey('interface.address.home')),
            self::PROFILE_NAME_OFFICE => trans(LanguageHelper::getTranslationKey('interface.address.office')),
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }

    public static function getByEmail($email)
    {
        if (empty($email)) {
            return null;
        }

        return RuntimeCache::getOrSet('customer_email_' . $email, function() use ($email) {
            // Find from flat index only
            $customer = Customer::flatFindBy('profile_email', $email);

            // if (empty($customer)) {
            //     $qb = self::whereField('email', $email);
            //     $customer = $qb->first();
            // }

            return $customer;
        });
    }

    public static function searchCustomers($keyword)
    {
        $qb = self::with('profile')
            ->joinFullName()
            ->joinFields(['email']);

        $qb->whereRaw('CONCAT(VFNAME.value, " ", VLNAME.value) LIKE ?', ['%'.$keyword.'%']);
        $qb->orWhereRaw('VFNAME.value LIKE ?', ['%'.$keyword.'%']);
        $qb->orWhereRaw('VLNAME.value LIKE ?', ['%'.$keyword.'%']);
        $qb->orWhereRaw('JOIN_email.value LIKE ?', ['%'.$keyword.'%']);

        return $qb->get();
    }
}
