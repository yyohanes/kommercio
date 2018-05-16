<?php

Route::prefix('public')
    ->namespace('Api\Frontend')
    ->group(function() {
        Route::prefix('store')
            ->namespace('Store')
            ->group(function() {

                Route::get('/stores', [
                    'as' => 'api.frontend.store.stores',
                    'uses' => 'StoreController@stores',
                ]);
            });
    });
