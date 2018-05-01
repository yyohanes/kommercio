<?php

Route::group(
    [
        'middleware' => [
            'api',
        ]
    ],
    function() {
        /**
         * To add new component api routes, register route file in $routes
         */

        $routes = [
            'products.php',
        ];

        foreach ($routes as $route) {
            require_once(base_path('routes/api/' . $route));
        }
    });
