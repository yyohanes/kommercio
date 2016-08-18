<?php

namespace Kommercio\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class DiscernAdminCustomer
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
        if (Auth::guard($guard)->check()) {
            $routeAction = $request->route()->getAction();

            $section = explode('.', $routeAction['as'])[0];

            $user = Auth::guard($guard)->user();

            if(!$user->isCustomer && $section != 'backend'){
                return redirect()->guest(route('backend.dashboard'));
            }elseif($user->isCustomer && $section != 'frontend'){
                return redirect()->guest(route('frontend.member.account'));
            }
        }

        return $next($request);
    }
}
