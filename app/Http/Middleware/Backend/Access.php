<?php

namespace Kommercio\Http\Middleware\Backend;

use Closure;
use Illuminate\Support\Facades\Auth;

class Access
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
        $route = $request->route();
        $routeAction = $route->getAction();
        $permissions = !empty($routeAction['permissions'])?$routeAction['permissions']:null;

        $user = Auth::guard($guard)->user();

        if(!empty($permissions) && $user){
            $allowed = TRUE;
            foreach($permissions as $permission){
                $allowed = $user->can('access', [$permission]);

                if($allowed){
                    break;
                }
            }

            if(!$allowed){
                return redirect()->back()->withErrors(['You are not authorized to do this action.']);
            }
        }

        return $next($request);
    }
}
