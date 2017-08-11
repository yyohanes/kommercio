<?php

namespace Kommercio\Http\Middleware\Frontend;

use Carbon\Carbon;
use Closure;
use Kommercio\Facades\ProjectHelper;

class CacheControl
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
        $response = $next($request);

        $route = $request->route();
        $routeAction = $route->getAction();
        $cacheable = isset($routeAction['cache_control_exclude'])?$routeAction['cache_control_exclude']:true;

        if ($cacheable) {
            // TODO: Cache config should be categorizeable
            $response->header('Cache-Control', $this->buildCacheControl('default'));
        }

        return $response;
    }

    private function buildCacheControl($config)
    {
        $cacheConfig = ProjectHelper::getConfig('cache_control.'.$config);

        $cacheParts = [];

        foreach ($cacheConfig as $key => $cacheConfigItem) {
            switch ($key) {
                case 'public':
                    if ($cacheConfigItem) {
                        $cacheParts[] = $key;
                    }
                    break;
                default:
                    $cacheParts[] = $key.'='.$cacheConfigItem;
                    break;
            }
        }

        return implode(', ', $cacheParts);
    }
}
