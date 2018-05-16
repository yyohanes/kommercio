<?php

Route::prefix('public')
    ->namespace('Api\Frontend')
    ->group(function() {
        Route::prefix('auth')
            ->namespace('Auth')
            ->group(function() {

                Route::post('/login', [
                    'as' => 'api.frontend.auth.login',
                    'uses' => 'LoginController@login',
                ]);

                Route::post('/refresh', [
                    'as' => 'api.frontend.auth.refresh',
                    'uses' => 'LoginController@refresh',
                ]);

                Route::post('/logout', [
                    'as' => 'api.frontend.auth.logout',
                    'uses' => 'LoginController@logout',
                ]);

                Route::middleware(['auth:api'])
                    ->group(function() {
                        Route::get('/me', [
                            'as' => 'api.frontend.auth.me',
                            'uses' => 'AuthController@me',
                        ]);
                    });
            });
    });
