<?php

namespace Kommercio\Http\Middleware\Frontend;

use Carbon\Carbon;
use Closure;

class CustomerActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $user = $request->user($guard);
        $customer = $user && $user->customer?$user->customer:null;

        if($customer){
            $customer->update([
                'last_active' => Carbon::now()
            ]);
        }

        return $next($request);
    }
}
