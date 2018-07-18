<?php

Route::prefix('public')
    ->namespace('Api\Frontend')
    ->group(function() {
        Route::prefix('banner')
            ->namespace('Banner')
            ->group(function() {

                Route::get('/group', [
                    'as' => 'api.frontend.banner.group.get',
                    'uses' => 'BannerGroupController@get',
                ]);
            });
    });
