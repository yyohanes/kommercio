<?php

Route::prefix('public')
    ->namespace('Api\Frontend')
    ->group(function() {
        Route::prefix('newsletter')
            ->namespace('Newsletter')
            ->group(function() {

                Route::post('/subscribe', [
                    'as' => 'api.frontend.newsletter.subscribe',
                    'uses' => 'NewsletterController@subscribe',
                ]);
            });
    });
