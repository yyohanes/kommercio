<?php

Route::prefix('public')
    ->namespace('Api\Frontend')
    ->group(function() {
        Route::prefix('order')
            ->namespace('Order')
            ->group(function() {

                /**
                 * Get available shipping methods
                 * @param {int} store_id
                 * @param {int} shipping_method
                 * @param {array} quantities
                 * @param {int} quantities.*
                 * @param {array} products
                 * @param {int} products.*
                 */
                Route::get('/shipping-methods', [
                    'as' => 'api.frontend.order.shipping_methods',
                    'uses' => 'OrderController@shippingMethods',
                ]);

                /**
                 * Get available payment methods
                 * @param {int} store_id
                 */
                Route::get('/payment-methods', [
                    'as' => 'api.frontend.order.payment_methods',
                    'uses' => 'OrderController@paymentMethods',
                ]);

                /**
                 * Returns unavailable dates of given dates
                 *
                 * @param {array} products ordered products. array key is product id and value is quantity
                 * eg: products[3] = 10; products[4] = 1
                 * @param {dates} dates list of dates in Y-m-d format to check
                 * @param {int} store_id store to check
                 */
                Route::get('/availability', [
                    'as' => 'api.frontend.order.availability',
                    'uses' => 'OrderController@availability',
                ]);

                /**
                 * Returns available times of given dates
                 *
                 * @param {array} products ordered products. array key is product id and value is quantity
                 * eg: products[3] = 10; products[4] = 1
                 * @param {dates} dates list of dates in Y-m-d format to check
                 * @param {int} store_id store to check
                 * @param {int} shipping_method_id shipping method to check
                 * @param {int} shipping_method_option shipping method option to check
                 */
                Route::get('/days-availability', [
                    'as' => 'api.frontend.order.days_availability',
                    'uses' => 'OrderController@daysAvailability',
                ]);

                /**
                 * Get per-order limits
                 * @param {int} store_id
                 * @param {date} date format Y-m-d
                 * @param {string} products comma-delimited product ids
                 * @param {string} product_categories comma-delimited product category ids
                 */
                Route::get('/limit', [
                    'as' => 'api.frontend.order.limit',
                    'uses' => 'OrderController@getOrderLimit',
                ]);

                Route::post('/submit', [
                    'as' => 'api.frontend.order.submit',
                    'uses' => 'OrderController@submit',
                ]);
            });
    });
