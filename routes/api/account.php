<?php

Route::prefix('public')
    ->namespace('Api\Frontend')
    ->group(function() {
        Route::prefix('account')
            ->namespace('Account')
            ->group(function() {

                Route::middleware(['auth:api'])
                    ->group(function() {

                        
                    });
            });
    });
