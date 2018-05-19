<?php

Route::prefix('public')
    ->namespace('Api\Frontend')
    ->group(function() {
        Route::prefix('page')
            ->namespace('Page')
            ->group(function() {

                Route::get('/', [
                    'as' => 'api.frontend.page.get',
                    'uses' => 'PageController@get',
                ]);
            });
    });
