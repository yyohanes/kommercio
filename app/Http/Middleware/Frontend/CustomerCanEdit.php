<?php

namespace Kommercio\Http\Middleware\Frontend;

use Closure;
use Kommercio\Facades\LanguageHelper;

class CustomerCanEdit
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
        $customer = $user->customer;

        $routeAction = $request->route()->getAction();

        if(strpos($routeAction['as'], 'frontend.member.address.') == 0 && $request->route('id')){
            if(!in_array($request->route('id'), $customer->savedProfiles->pluck('id')->all())){
                return redirect()->route('frontend.member.address.index')->withErrors([trans(LanguageHelper::getTranslationKey('frontend.general.not_allowed'))]);
            }
        }

        return $next($request);
    }
}
