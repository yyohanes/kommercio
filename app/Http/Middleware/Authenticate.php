<?php

namespace Kommercio\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Authenticate
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
        if (Auth::guard($guard)->guest()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            } else {
                $routeAction = $request->route()->getAction();

                if(isset($routeAction['middleware']) && is_array($routeAction['middleware'])){
                    if(in_array('backend.auth', $routeAction['middleware'])){
                        return redirect()->guest(route('backend.login_form'));
                    }elseif(in_array('auth', $routeAction['middleware'])){
                        return redirect()->guest(route('frontend.login_form'));
                    }
                }

                return redirect()->guest('login');
            }
        }

        return $next($request);
    }
}
