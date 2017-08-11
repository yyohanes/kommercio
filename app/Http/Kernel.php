<?php

namespace Kommercio\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Kommercio\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \Kommercio\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \Kommercio\Http\Middleware\UrlAlias::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \Kommercio\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Kommercio\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:60,1',
            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Kommercio\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'guest' => \Kommercio\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'backend.auth' => \Kommercio\Http\Middleware\Authenticate::class,
        'backend.guest' => \Kommercio\Http\Middleware\RedirectIfAuthenticated::class,
        'backend.order_editable' => \Kommercio\Http\Middleware\Backend\OrderEditable::class,
        'backend.order_deleteable' => \Kommercio\Http\Middleware\Backend\OrderDeleteable::class,
        'backend.access' => \Kommercio\Http\Middleware\Backend\Access::class,
        'frontend.customer_can_edit' => \Kommercio\Http\Middleware\Frontend\CustomerCanEdit::class,
        'frontend.customer_activity' => \Kommercio\Http\Middleware\Frontend\CustomerActivity::class,
        'discern_admin_customer' => \Kommercio\Http\Middleware\DiscernAdminCustomer::class,
        'cache_control' => \Kommercio\Http\Middleware\Frontend\CacheControl::class,
    ];
}
