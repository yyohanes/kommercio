<?php

Route::prefix('public')
    ->namespace('Api\Frontend')
    ->group(function() {
        Route::prefix('customer')
            ->namespace('Customer')
            ->group(function() {

                Route::post('/create', [
                    'as' => 'api.frontend.customer.create',
                    'uses' => 'CustomerController@create',
                ]);

                Route::middleware(['auth:api'])
                    ->group(function() {
                        Route::post('/update', [
                            'as' => 'api.frontend.customer.update',
                            'uses' => 'CustomerController@update',
                        ]);
                    });
            });
    });
