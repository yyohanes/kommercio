<?php

Route::prefix('public')
    ->namespace('Api\Frontend')
    ->group(function() {
        Route::prefix('products')
            ->namespace('Products')
            ->group(function() {
                Route::get('/', [
                    'as' => 'api.frontend.products.index',
                    'uses' => 'ProductController@index',
                ]);
            });
    });
