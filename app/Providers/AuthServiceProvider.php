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

        $gate->define('access', function ($user, $permission, $model = null) {
            if(empty($permission)){
                return TRUE;
            }

            return $user->role->hasPermission($permission);
        });

        $gate->before(function ($user, $ability) {
            if ($user->isSuperAdmin) {
                return true;
            }
        });
    }
}
