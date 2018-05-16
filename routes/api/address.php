<?php

Route::prefix('public')
    ->namespace('Api\Frontend')
    ->group(function() {
        Route::prefix('address')
            ->namespace('Address')
            ->group(function() {

                /**
                 * Get address options
                 * @param {int} parent
                 * @param {bool} first_option
                 * @param {bool} active_only
                 */
                Route::get('{type}/options', [
                    'as' => 'api.frontend.address.options',
                    'uses' => 'AddressController@options',
                ]);
            });
    });

