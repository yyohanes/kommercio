<?php

namespace Kommercio\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode as BaseCheckForMaintenanceMode;
use Closure;

class CheckForMaintenanceMode extends BaseCheckForMaintenanceMode {

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function handle($request, Closure $next)
    {
        if ($this->app->isDownForMaintenance()) {
            $cookieName = config('project.project_machine_name').'_maintenance_bypass';
            $cookieValue = config('project.project_machine_name').'123';

            // If on maintenance but enter using secret key, set cookie
            if ($request->get('secret_chamber', '') === (config('project.project_machine_name').'123')) {
                return redirect($request->getPathInfo())
                    ->withCookie(cookie($cookieName, $cookieValue, 60));
            }

            // If on maintenance and bypass cookie exists
            if ($request->hasCookie($cookieName)) {
                $decyptedCookieValue = $request->cookie($cookieName);

                if ($decyptedCookieValue === $cookieValue)
                    return $next($request);
            }
        }

        return parent::handle($request, $next);
    }
}