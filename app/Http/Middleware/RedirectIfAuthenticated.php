<?php

namespace Kommercio\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
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

            if(isset($routeAction['middleware']) && is_array($routeAction['middleware'])){
                if(in_array('backend.access', $routeAction['middleware'])){
                    return redirect()->guest(route('backend.dashboard'));
                }else{
                    return redirect()->guest(route('frontend.member.account'));
                }
            }

            return redirect('/');
        }

        return $next($request);
    }
}
