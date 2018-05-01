<?php

namespace Kommercio\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     * Common uses: Exclude external payment notification
     * @var array
     */
    protected $except = [
        'payment-method/midtrans/snap/notify',
        'checkout/payment/*/notify',
        'api/*',
    ];
}
