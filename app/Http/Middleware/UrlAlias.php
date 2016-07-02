<?php

namespace Kommercio\Http\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;

class UrlAlias
{
    /**
     * The application implementation.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

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
        $request_uri = $request->server->get('REQUEST_URI');
        $request_uri_string = urldecode(substr($request_uri,1));

        if(strlen($request->getQueryString()) > 0){
            $query = '?'.$request->getQueryString();
            $path = str_replace($query, '', $request_uri_string);
        }else{
            $path = $request_uri_string;
            $query = '';
        }

        if(strlen($path) > 1){
            $urlAlias = \Kommercio\Models\UrlAlias::where('external_path', $path)->first();

            if($urlAlias){
                $request->server->set('REQUEST_URI', '/'.$urlAlias->internal_path);
            }
        }

        return $next($request);
    }
}
