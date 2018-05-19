<?php

Route::prefix('public')
    ->namespace('Api\Frontend')
    ->group(function() {
        Route::prefix('menu')
            ->namespace('Menu')
            ->group(function() {

                Route::get('/', [
                    'as' => 'api.frontend.menu.get',
                    'uses' => 'MenuController@get',
                ]);
            });
    });
