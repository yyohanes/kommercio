<?php

namespace Kommercio\Providers;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Policies\AccessPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [];

    /**
     * Register any application authentication / authorization services.
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate  $gate
     * @return void
     */
    public function boot(GateContract $gate)
    {
        $this->registerPolicies($gate);

        $gate->define('access', function ($user, $permission) {
            if(empty($permission)){
                return TRUE;
            }

            return $user->role->hasPermission($permission);
        });

        $gate->define('process_order', function ($user, $order, $process_type) {
            $orderProcessConditions = config('project.order_process_condition', config('kommercio.order_process_condition'));

            if(!isset($orderProcessConditions[$process_type])){
                return FALSE;
            }

            $valid = TRUE;

            foreach($orderProcessConditions[$process_type] as $type => $condition){
                switch($type){
                    case 'status':
                        $valid = in_array($order->status, $condition);
                        break;
                    case 'printed':
                        $internalMemos = $order->internalMemos;

                        foreach($internalMemos as $internalMemo){
                            if(in_array($internalMemo->getData('key', ''), ['print_invoice', 'print_delivery_note'])){
                                $valid = true;
                                break;
                            }
                        }

                        if(!$condition){
                            $valid = !$valid;
                        }

                        break;
                    case 'outstanding':
                        $valid = $order->getOutstandingAmount() <= $condition;
                        break;
                    default:
                        $valid = false;
                        break;
                }

                if(!$valid){
                    break;
                }
            }

            return $valid;
        });

        $gate->before(function ($user, $ability) {
            if ($ability == 'access' && $user->isSuperAdmin) {
                return true;
            }
        });
    }
}
