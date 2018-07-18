<?php

Route::prefix('public')
    ->namespace('Api\Frontend')
    ->group(function() {
        Route::prefix('block')
            ->namespace('Block')
            ->group(function() {

                Route::get('/', [
                    'as' => 'api.frontend.block.get',
                    'uses' => 'BlockController@get',
                ]);
            });
    });
