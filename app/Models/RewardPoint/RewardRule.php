<?php

namespace Kommercio\Models\RewardPoint;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;
use Kommercio\Models\Interfaces\StoreManagedInterface;
use Kommercio\Models\Order\Order;
use Kommercio\Models\User;
use Kommercio\Traits\Model\AuthorSignature;
use Kommercio\Traits\Model\HasDataColumn;
use Kommercio\Traits\Model\ToggleDate;

class RewardRule extends Model implements AuthorSignatureInterface, StoreManagedInterface
{
    use AuthorSignature, HasDataColumn, ToggleDate;

    const TYPE_PER_ORDER = 'per_order';

    protected $fillable = ['name', 'type', 'reward', 'currency', 'sort_order', 'data', 'active', 'member'];
    protected $toggleFields = ['active'];
    protected $casts = [
        'member' => 'boolean'
    ];

    public function checkStorePermissionByUser(User $user)
    {
        if($user->manageAllStores){
            return true;
        }

        return $this->store_id && in_array($this->store_id, $user->getManagedStores()->pluck('id')->all());
    }

    public function calculateRewardPoint(Order $order)
    {
        if($this->member && !($order->customer && $order->customer->user)){
            return 0;
        }

        $rule = $this->getData('rule');

        switch($this->type){
            case 'per_order':
                $gross = $order->getTotalBeforeExtras();

                if($rule['include_shipping']){
                    $gross += $order->shipping_total;
                }

                if($rule['include_tax']){
                    $gross += $order->tax_total;
                }

                return floor($gross / $rule['order_step_amount']) * $this->reward;
                break;
        }
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

    //Static
    public static function getRewardRules($options)
    {
        $qb = self::orderBy('sort_order', 'ASC')->active();

        $currency = !empty($options['currency'])?$options['currency']:null;
        $store = !empty($options['store_id'])?$options['store_id']:null;

        $qb->where(function($qb) use ($currency){
            $qb->whereNull('currency');

            if($currency){
                $qb->orWhere('currency', $currency);
            }
        });

        $qb->where(function($qb) use ($store){
            $qb->whereNull('store_id');

            if($store){
                $qb->orWhere('store_id', $store);
            }
        });

        return $qb->get();
    }

    public static function getTypeOptions($option=null, $all=false)
    {
        $array = [
            self::TYPE_PER_ORDER => 'Per Order',
        ];

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }
}
