<?php

namespace Kommercio\Http\Middleware\Frontend;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Response;
use Kommercio\Facades\ProjectHelper;

class CacheControl
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string $config
     * @return mixed
     */
    public function handle($request, Closure $next, $config = 'default')
    {
        $response = $next($request);
        $this->buildCacheControl($response, $config);

        return $response;
    }

    private function buildCacheControl(Response $response, $config)
    {
        $cacheConfig = ProjectHelper::getConfig('cache_control.'.$config);

        foreach ($cacheConfig as $key => $cacheConfigItem) {
            $response->headers->addCacheControlDirective($key, $cacheConfigItem);
        }
    }
}
