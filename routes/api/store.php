<?php

Route::prefix('public')
    ->namespace('Api\Frontend')
    ->group(function() {
        Route::prefix('store')
            ->namespace('Store')
            ->group(function() {

                Route::get('/', [
                    'as' => 'api.frontend.store.get',
                    'uses' => 'StoreController@get',
                ]);
            });
    });
