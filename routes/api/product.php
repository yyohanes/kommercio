<?php

Route::prefix('public')
    ->namespace('Api\Frontend')
    ->group(function() {
        Route::prefix('product')
            ->namespace('Product')
            ->group(function() {

                /**
                 * @param {string} categories comma-delimited category ids
                 */
                Route::get('/products', [
                    'as' => 'api.frontend.product.products',
                    'uses' => 'ProductController@products',
                ]);
            });
    });
