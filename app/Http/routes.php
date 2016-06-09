<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {
    Route::group(['prefix' => config('kommercio.backend_prefix'), 'namespace' => 'Backend'], function(){
        // Authentication Routes...
        Route::get('login', [
            'as' => 'backend.login_form',
            'uses' => 'Auth\AuthController@showLoginForm'
        ]);

        Route::post('login', [
            'as' => 'backend.login',
            'uses' => 'Auth\AuthController@login'
        ]);

        Route::get('logout', [
            'as' => 'backend.logout',
            'uses' => 'Auth\AuthController@logout'
        ]);

        // Password Reset Routes...
        Route::get('password/reset/{token?}', [
            'as' => 'backend.password.form',
            'uses' => 'Auth\PasswordController@showResetForm'
        ]);

        Route::post('password/email', [
            'as' => 'backend.password.email',
            'uses' => 'Auth\PasswordController@sendResetLinkEmail'
        ]);

        Route::post('password/reset', [
            'as' => 'backend.password.reset',
            'uses' => 'Auth\PasswordController@reset'
        ]);

        Route::group(['middleware' => ['backend.auth']], function(){
            Route::get('/', [
                'as' => 'backend.dashboard',
                'uses' => 'ChamberController@dashboard'
            ]);

            Route::group(['prefix' => 'account'], function(){
                Route::any('settings/credentials', [
                    'as' => 'backend.account.credentials',
                    'uses' => 'AccountController@credentials'
                ]);

                Route::any('settings/profile', [
                    'as' => 'backend.account.profile',
                    'uses' => 'AccountController@profile'
                ]);
            });

            Route::group(['prefix' => 'file'], function(){
                Route::post('upload', [
                    'as' => 'backend.file.upload',
                    'uses' => 'FileController@upload'
                ]);
            });

            //Catalog
            Route::group(['prefix' => 'catalog', 'namespace' => 'Catalog'], function(){
                Route::group(['prefix' => 'category'], function(){
                    Route::get('index/{parent?}', [
                        'as' => 'backend.catalog.category.index',
                        'uses' => 'CategoryController@index'
                    ]);

                    Route::get('create', [
                        'as' => 'backend.catalog.category.create',
                        'uses' => 'CategoryController@create'
                    ]);

                    Route::post('store', [
                        'as' => 'backend.catalog.category.store',
                        'uses' => 'CategoryController@store'
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.catalog.category.edit',
                        'uses' => 'CategoryController@edit'
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.catalog.category.update',
                        'uses' => 'CategoryController@update'
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.catalog.category.delete',
                        'uses' => 'CategoryController@delete'
                    ]);

                    Route::post('reorder', [
                        'as' => 'backend.catalog.category.reorder',
                        'uses' => 'CategoryController@reorder'
                    ]);

                    Route::get('autocomplete', [
                        'as' => 'backend.catalog.category.autocomplete',
                        'uses' => 'CategoryController@autocomplete'
                    ]);
                });

                Route::group(['prefix' => 'product'], function(){
                    Route::any('index', [
                        'as' => 'backend.catalog.product.index',
                        'uses' => 'ProductController@index'
                    ]);

                    Route::get('create', [
                        'as' => 'backend.catalog.product.create',
                        'uses' => 'ProductController@create'
                    ]);

                    Route::post('store', [
                        'as' => 'backend.catalog.product.store',
                        'uses' => 'ProductController@store'
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.catalog.product.edit',
                        'uses' => 'ProductController@edit'
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.catalog.product.update',
                        'uses' => 'ProductController@update'
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.catalog.product.delete',
                        'uses' => 'ProductController@delete'
                    ]);

                    Route::post('{id}/feature/index', [
                        'as' => 'backend.catalog.product.feature_index',
                        'uses' => 'ProductController@featureIndex'
                    ]);

                    Route::get('{id}/variation/index', [
                        'as' => 'backend.catalog.product.variation_index',
                        'uses' => 'ProductController@variationIndex'
                    ]);

                    Route::post('{id}/variation/form/{variation_id?}', [
                        'as' => 'backend.catalog.product.variation_form',
                        'uses' => 'ProductController@variationForm'
                    ]);

                    Route::post('{id}/variation/save/{variation_id?}', [
                        'as' => 'backend.catalog.product.variation_save',
                        'uses' => 'ProductController@variationSave'
                    ]);

                    Route::get('autocomplete', [
                        'as' => 'backend.catalog.product.autocomplete',
                        'uses' => 'ProductController@autocomplete'
                    ]);
                });

                Route::group(['prefix' => 'product-attribute'], function(){
                    Route::any('index', [
                        'as' => 'backend.catalog.product_attribute.index',
                        'uses' => 'ProductAttributeController@index'
                    ]);

                    Route::get('create', [
                        'as' => 'backend.catalog.product_attribute.create',
                        'uses' => 'ProductAttributeController@create'
                    ]);

                    Route::post('store', [
                        'as' => 'backend.catalog.product_attribute.store',
                        'uses' => 'ProductAttributeController@store'
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.catalog.product_attribute.edit',
                        'uses' => 'ProductAttributeController@edit'
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.catalog.product_attribute.update',
                        'uses' => 'ProductAttributeController@update'
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.catalog.product_attribute.delete',
                        'uses' => 'ProductAttributeController@delete'
                    ]);

                    Route::post('reorder', [
                        'as' => 'backend.catalog.product_attribute.reorder',
                        'uses' => 'ProductAttributeController@reorder'
                    ]);

                    Route::group(['prefix' => 'value/{attribute_id}'], function(){
                        Route::any('index', [
                            'as' => 'backend.catalog.product_attribute.value.index',
                            'uses' => 'ProductAttributeValueController@index'
                        ]);

                        Route::get('create', [
                            'as' => 'backend.catalog.product_attribute.value.create',
                            'uses' => 'ProductAttributeValueController@create'
                        ]);

                        Route::post('store', [
                            'as' => 'backend.catalog.product_attribute.value.store',
                            'uses' => 'ProductAttributeValueController@store'
                        ]);

                        Route::get('edit/{id}', [
                            'as' => 'backend.catalog.product_attribute.value.edit',
                            'uses' => 'ProductAttributeValueController@edit'
                        ]);

                        Route::post('update/{id}', [
                            'as' => 'backend.catalog.product_attribute.value.update',
                            'uses' => 'ProductAttributeValueController@update'
                        ]);

                        Route::post('delete/{id}', [
                            'as' => 'backend.catalog.product_attribute.value.delete',
                            'uses' => 'ProductAttributeValueController@delete'
                        ]);

                        Route::post('reorder', [
                            'as' => 'backend.catalog.product_attribute.value.reorder',
                            'uses' => 'ProductAttributeValueController@reorder'
                        ]);
                    });
                });

                Route::group(['prefix' => 'product-feature'], function(){
                    Route::any('index', [
                        'as' => 'backend.catalog.product_feature.index',
                        'uses' => 'ProductFeatureController@index'
                    ]);

                    Route::get('create', [
                        'as' => 'backend.catalog.product_feature.create',
                        'uses' => 'ProductFeatureController@create'
                    ]);

                    Route::post('store', [
                        'as' => 'backend.catalog.product_feature.store',
                        'uses' => 'ProductFeatureController@store'
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.catalog.product_feature.edit',
                        'uses' => 'ProductFeatureController@edit'
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.catalog.product_feature.update',
                        'uses' => 'ProductFeatureController@update'
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.catalog.product_feature.delete',
                        'uses' => 'ProductFeatureController@delete'
                    ]);

                    Route::post('reorder', [
                        'as' => 'backend.catalog.product_feature.reorder',
                        'uses' => 'ProductFeatureController@reorder'
                    ]);

                    Route::group(['prefix' => 'value/{feature_id}'], function(){
                        Route::any('index', [
                            'as' => 'backend.catalog.product_feature.value.index',
                            'uses' => 'ProductFeatureValueController@index'
                        ]);

                        Route::get('create', [
                            'as' => 'backend.catalog.product_feature.value.create',
                            'uses' => 'ProductFeatureValueController@create'
                        ]);

                        Route::post('store', [
                            'as' => 'backend.catalog.product_feature.value.store',
                            'uses' => 'ProductFeatureValueController@store'
                        ]);

                        Route::get('edit/{id}', [
                            'as' => 'backend.catalog.product_feature.value.edit',
                            'uses' => 'ProductFeatureValueController@edit'
                        ]);

                        Route::post('update/{id}', [
                            'as' => 'backend.catalog.product_feature.value.update',
                            'uses' => 'ProductFeatureValueController@update'
                        ]);

                        Route::post('delete/{id}', [
                            'as' => 'backend.catalog.product_feature.value.delete',
                            'uses' => 'ProductFeatureValueController@delete'
                        ]);

                        Route::post('reorder', [
                            'as' => 'backend.catalog.product_feature.value.reorder',
                            'uses' => 'ProductFeatureValueController@reorder'
                        ]);
                    });
                });

                Route::group(['prefix' => 'manufacturer'], function(){
                    Route::get('index', [
                        'as' => 'backend.catalog.manufacturer.index',
                        'uses' => 'ManufacturerController@index'
                    ]);

                    Route::get('create', [
                        'as' => 'backend.catalog.manufacturer.create',
                        'uses' => 'ManufacturerController@create'
                    ]);

                    Route::post('store', [
                        'as' => 'backend.catalog.manufacturer.store',
                        'uses' => 'ManufacturerController@store'
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.catalog.manufacturer.edit',
                        'uses' => 'ManufacturerController@edit'
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.catalog.manufacturer.update',
                        'uses' => 'ManufacturerController@update'
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.catalog.manufacturer.delete',
                        'uses' => 'ManufacturerController@delete'
                    ]);
                });
            });

            //Price Rule
            Route::group(['prefix' => 'price-rule', 'namespace' => 'PriceRule'], function(){
                Route::group(['prefix' => 'product'], function(){
                    Route::get('{product_id}/mini-index', [
                        'as' => 'backend.price_rule.product.mini_index',
                        'uses' => 'ProductPriceRuleController@mini_index'
                    ]);

                    Route::post('{product_id}/mini-form/{id?}', [
                        'as' => 'backend.price_rule.product.mini_form',
                        'uses' => 'ProductPriceRuleController@mini_form'
                    ]);

                    Route::post('{product_id}/mini-save/{id?}', [
                        'as' => 'backend.price_rule.product.mini_save',
                        'uses' => 'ProductPriceRuleController@mini_save'
                    ]);

                    Route::get('index', [
                        'as' => 'backend.price_rule.product.index',
                        'uses' => 'ProductPriceRuleController@index'
                    ]);

                    Route::get('create', [
                        'as' => 'backend.price_rule.product.create',
                        'uses' => 'ProductPriceRuleController@create'
                    ]);

                    Route::post('store', [
                        'as' => 'backend.price_rule.product.store',
                        'uses' => 'ProductPriceRuleController@store'
                    ]);

                    Route::get('{id}/edit', [
                        'as' => 'backend.price_rule.product.edit',
                        'uses' => 'ProductPriceRuleController@edit'
                    ]);

                    Route::post('{id}/update', [
                        'as' => 'backend.price_rule.product.update',
                        'uses' => 'ProductPriceRuleController@update'
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.price_rule.product.delete',
                        'uses' => 'ProductPriceRuleController@delete'
                    ]);

                    Route::post('reorder', [
                        'as' => 'backend.price_rule.product.reorder',
                        'uses' => 'ProductPriceRuleController@reorder'
                    ]);
                });

                Route::group(['prefix' => 'cart'], function(){
                    Route::get('index', [
                        'as' => 'backend.price_rule.cart.index',
                        'uses' => 'CartPriceRuleController@index'
                    ]);

                    Route::get('create', [
                        'as' => 'backend.price_rule.cart.create',
                        'uses' => 'CartPriceRuleController@create'
                    ]);

                    Route::post('store', [
                        'as' => 'backend.price_rule.cart.store',
                        'uses' => 'CartPriceRuleController@store'
                    ]);

                    Route::get('{id}/edit', [
                        'as' => 'backend.price_rule.cart.edit',
                        'uses' => 'CartPriceRuleController@edit'
                    ]);

                    Route::post('{id}/update', [
                        'as' => 'backend.price_rule.cart.update',
                        'uses' => 'CartPriceRuleController@update'
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.price_rule.cart.delete',
                        'uses' => 'CartPriceRuleController@delete'
                    ]);

                    Route::post('reorder', [
                        'as' => 'backend.price_rule.cart.reorder',
                        'uses' => 'CartPriceRuleController@reorder'
                    ]);
                });
            });

            //Order
            Route::group(['prefix' => 'sales', 'namespace' => 'Sales'], function(){
                Route::group(['prefix' => 'order'], function(){
                    Route::any('index', [
                        'as' => 'backend.sales.order.index',
                        'uses' => 'OrderController@index'
                    ]);

                    Route::get('create', [
                        'as' => 'backend.sales.order.create',
                        'uses' => 'OrderController@create'
                    ]);

                    Route::post('store', [
                        'as' => 'backend.sales.order.store',
                        'uses' => 'OrderController@store'
                    ]);

                    Route::get('view/{id}', [
                        'as' => 'backend.sales.order.view',
                        'uses' => 'OrderController@view'
                    ]);

                    Route::get('delete/all', [
                        'as' => 'backend.sales.order.delete_all',
                        'uses' => 'OrderController@deleteAll'
                    ]);

                    Route::group(['middleware' => ['backend.order_editable']], function(){
                        Route::get('edit/{id}', [
                            'as' => 'backend.sales.order.edit',
                            'uses' => 'OrderController@edit'
                        ]);

                        Route::post('update/{id}', [
                            'as' => 'backend.sales.order.update',
                            'uses' => 'OrderController@update'
                        ]);

                        Route::post('delete/{id}', [
                            'as' => 'backend.sales.order.delete',
                            'uses' => 'OrderController@delete',
                            'middleware' => ['backend.order_deleteable']
                        ]);
                    });

                    Route::any('process/{action}/{id?}', [
                        'as' => 'backend.sales.order.process',
                        'uses' => 'OrderController@process'
                    ]);

                    Route::post('copy/customer_information/{type}/{profile_id?}', [
                        'as' => 'backend.sales.order.copy_customer_information',
                        'uses' => 'OrderController@copyCustomerInformation'
                    ]);

                    Route::post('line_item/{type}/row/{id?}', [
                        'as' => 'backend.sales.order.line_item.row',
                        'uses' => 'OrderController@lineItemRow'
                    ]);

                    Route::any('shipping/options', [
                        'as' => 'backend.sales.order.shipping_options',
                        'uses' => 'OrderController@shippingOptions'
                    ]);

                    Route::post('order-cart-rules/get', [
                        'as' => 'backend.sales.order.get_cart_rules',
                        'uses' => 'OrderController@getCartRules'
                    ]);

                    Route::group(['prefix' => 'payment'], function(){
                        Route::get('{order_id}/index', [
                            'as' => 'backend.sales.order.payment.index',
                            'uses' => 'PaymentController@orderPaymentIndex'
                        ]);

                        Route::get('{order_id}/form', [
                            'as' => 'backend.sales.order.payment.form',
                            'uses' => 'PaymentController@orderPaymentForm'
                        ]);

                        Route::post('{order_id}/save', [
                            'as' => 'backend.sales.order.payment.save',
                            'uses' => 'PaymentController@orderPaymentSave'
                        ]);

                        Route::any('process/{action}/{id}', [
                            'as' => 'backend.sales.order.payment.process',
                            'uses' => 'PaymentController@process'
                        ]);
                    });
                });

                //Order Limits
                Route::group(['prefix' => 'order-limit'], function(){
                    Route::get('{type}/index', [
                        'as' => 'backend.order_limit.index',
                        'uses' => 'OrderLimitController@index'
                    ]);

                    Route::get('{type}/create', [
                        'as' => 'backend.order_limit.create',
                        'uses' => 'OrderLimitController@create'
                    ]);

                    Route::post('{type}/store', [
                        'as' => 'backend.order_limit.store',
                        'uses' => 'OrderLimitController@store'
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.order_limit.edit',
                        'uses' => 'OrderLimitController@edit'
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.order_limit.update',
                        'uses' => 'OrderLimitController@update'
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.order_limit.delete',
                        'uses' => 'OrderLimitController@delete'
                    ]);
                });
            });

            //Configurations
            Route::group(['prefix' => 'configuration'], function(){
                //Store
                Route::group(['prefix' => 'store', 'namespace' => 'Store'], function(){
                    Route::get('index', [
                        'as' => 'backend.store.index',
                        'uses' => 'StoreController@index'
                    ]);

                    Route::get('create', [
                        'as' => 'backend.store.create',
                        'uses' => 'StoreController@create'
                    ]);

                    Route::post('store', [
                        'as' => 'backend.store.store',
                        'uses' => 'StoreController@store'
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.store.edit',
                        'uses' => 'StoreController@edit'
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.store.update',
                        'uses' => 'StoreController@update'
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.store.delete',
                        'uses' => 'StoreController@delete'
                    ]);
                });

                //Payment Methods
                Route::group(['prefix' => 'payment-method', 'namespace' => 'PaymentMethod'], function(){
                    Route::get('index', [
                        'as' => 'backend.payment_method.index',
                        'uses' => 'PaymentMethodController@index'
                    ]);

                    Route::get('create', [
                        'as' => 'backend.payment_method.create',
                        'uses' => 'PaymentMethodController@create'
                    ]);

                    Route::post('store', [
                        'as' => 'backend.payment_method.store',
                        'uses' => 'PaymentMethodController@store'
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.payment_method.edit',
                        'uses' => 'PaymentMethodController@edit'
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.payment_method.update',
                        'uses' => 'PaymentMethodController@update'
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.payment_method.delete',
                        'uses' => 'PaymentMethodController@delete'
                    ]);

                    Route::post('reorder', [
                        'as' => 'backend.payment_method.reorder',
                        'uses' => 'PaymentMethodController@reorder'
                    ]);
                });

                //Shipping Methods
                Route::group(['prefix' => 'shipping-method', 'namespace' => 'ShippingMethod'], function(){
                    Route::get('index', [
                        'as' => 'backend.shipping_method.index',
                        'uses' => 'ShippingMethodController@index'
                    ]);

                    Route::get('create', [
                        'as' => 'backend.shipping_method.create',
                        'uses' => 'ShippingMethodController@create'
                    ]);

                    Route::post('store', [
                        'as' => 'backend.shipping_method.store',
                        'uses' => 'ShippingMethodController@store'
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.shipping_method.edit',
                        'uses' => 'ShippingMethodController@edit'
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.shipping_method.update',
                        'uses' => 'ShippingMethodController@update'
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.shipping_method.delete',
                        'uses' => 'ShippingMethodController@delete'
                    ]);

                    Route::post('reorder', [
                        'as' => 'backend.shipping_method.reorder',
                        'uses' => 'ShippingMethodController@reorder'
                    ]);
                });

                //Address
                Route::group(['prefix' => 'address', 'namespace' => 'Address'], function(){
                    Route::any('{type}/index', [
                        'as' => 'backend.configuration.address.index',
                        'uses' => 'AddressController@index'
                    ]);

                    Route::get('{type}/create', [
                        'as' => 'backend.configuration.address.create',
                        'uses' => 'AddressController@create'
                    ]);

                    Route::post('{type}/store', [
                        'as' => 'backend.configuration.address.store',
                        'uses' => 'AddressController@store'
                    ]);

                    Route::get('{type}/edit/{id}', [
                        'as' => 'backend.configuration.address.edit',
                        'uses' => 'AddressController@edit'
                    ]);

                    Route::post('{type}/update/{id}', [
                        'as' => 'backend.configuration.address.update',
                        'uses' => 'AddressController@update'
                    ]);

                    Route::post('{type}/delete/{id}', [
                        'as' => 'backend.configuration.address.delete',
                        'uses' => 'AddressController@delete'
                    ]);

                    Route::post('{type}/reorder', [
                        'as' => 'backend.configuration.address.reorder',
                        'uses' => 'AddressController@reorder'
                    ]);
                });

                //Taxes
                Route::group(['prefix' => 'tax', 'namespace' => 'Tax'], function(){
                    Route::get('index', [
                        'as' => 'backend.tax.index',
                        'uses' => 'TaxController@index'
                    ]);

                    Route::get('create', [
                        'as' => 'backend.tax.create',
                        'uses' => 'TaxController@create'
                    ]);

                    Route::post('store', [
                        'as' => 'backend.tax.store',
                        'uses' => 'TaxController@store'
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.tax.edit',
                        'uses' => 'TaxController@edit'
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.tax.update',
                        'uses' => 'TaxController@update'
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.tax.delete',
                        'uses' => 'TaxController@delete'
                    ]);

                    Route::post('reorder', [
                        'as' => 'backend.tax.reorder',
                        'uses' => 'TaxController@reorder'
                    ]);

                    Route::get('get', [
                        'as' => 'backend.tax.get',
                        'uses' => 'TaxController@get'
                    ]);

                    Route::get('country_children/{country_id?}', [
                        'as' => 'backend.tax.country_children',
                        'uses' => 'TaxController@countryChildren'
                    ]);
                });
            });

            //Warehouse
            Route::group(['prefix' => 'warehouse', 'namespace' => 'Warehouse'], function(){
                Route::get('index', [
                    'as' => 'backend.warehouse.index',
                    'uses' => 'WarehouseController@index'
                ]);

                Route::get('create', [
                    'as' => 'backend.warehouse.create',
                    'uses' => 'WarehouseController@create'
                ]);

                Route::post('store', [
                    'as' => 'backend.warehouse.store',
                    'uses' => 'WarehouseController@store'
                ]);

                Route::get('edit/{id}', [
                    'as' => 'backend.warehouse.edit',
                    'uses' => 'WarehouseController@edit'
                ]);

                Route::post('update/{id}', [
                    'as' => 'backend.warehouse.update',
                    'uses' => 'WarehouseController@update'
                ]);

                Route::post('delete/{id}', [
                    'as' => 'backend.warehouse.delete',
                    'uses' => 'WarehouseController@delete'
                ]);
            });

            //Customers
            Route::group(['prefix' => 'customer', 'namespace' => 'Customer'], function(){
                Route::any('index', [
                    'as' => 'backend.customer.index',
                    'uses' => 'CustomerController@index'
                ]);

                Route::get('autocomplete', [
                    'as' => 'backend.customer.autocomplete',
                    'uses' => 'CustomerController@autocomplete'
                ]);

                Route::get('create', [
                    'as' => 'backend.customer.create',
                    'uses' => 'CustomerController@create'
                ]);

                Route::post('store', [
                    'as' => 'backend.customer.store',
                    'uses' => 'CustomerController@store'
                ]);

                Route::get('edit/{id}', [
                    'as' => 'backend.customer.edit',
                    'uses' => 'CustomerController@edit'
                ]);

                Route::post('update/{id}', [
                    'as' => 'backend.customer.update',
                    'uses' => 'CustomerController@update'
                ]);

                Route::post('delete/{id}', [
                    'as' => 'backend.customer.delete',
                    'uses' => 'CustomerController@delete'
                ]);
            });
        });
    });

    Route::get('images/{style}/{image}', 'ImageController@style')->where('image', '.*');

    Route::get('address/{type}/options/{parent?}', 'AddressController@options');
});