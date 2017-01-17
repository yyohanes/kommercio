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
    Route::group(['namespace' => 'Frontend', 'middleware' => ['frontend.customer_activity', 'discern_admin_customer']], function(){
        // Authentication Routes...
        Route::get('login', [
            'as' => 'frontend.login_form',
            'uses' => 'Auth\AuthController@showLoginForm'
        ]);

        Route::post('login', [
            'as' => 'frontend.login',
            'uses' => 'Auth\AuthController@login'
        ]);

        Route::get('logout', [
            'as' => 'frontend.logout',
            'uses' => 'Auth\AuthController@getLogout'
        ]);

        // Password Reset Routes...
        Route::get('password/reset/{token?}', [
            'as' => 'frontend.password.form',
            'uses' => 'Auth\PasswordController@showResetForm'
        ]);

        Route::post('password/email', [
            'as' => 'frontend.password.email',
            'uses' => 'Auth\PasswordController@sendResetLinkEmail'
        ]);

        Route::post('password/reset', [
            'as' => 'frontend.password.reset',
            'uses' => 'Auth\PasswordController@reset'
        ]);

        // Register Routes...
        Route::get('register', [
            'as' => 'frontend.register_form',
            'uses' => 'Auth\AuthController@getRegister'
        ]);

        Route::post('register', [
            'as' => 'frontend.register',
            'uses' => 'Auth\AuthController@postRegister'
        ]);

        Route::post('password/reset', [
            'as' => 'frontend.password.reset',
            'uses' => 'Auth\PasswordController@reset'
        ]);

        Route::group(['middleware' => 'auth'], function(){
            Route::group(['prefix' => 'member'], function(){
                Route::get('account', [
                    'as' => 'frontend.member.account',
                    'uses' => 'AccountController@account'
                ]);

                Route::any('account/profile-update', [
                    'as' => 'frontend.member.account.profile_update',
                    'uses' => 'AccountController@profileUpdate'
                ]);

                Route::any('account/account-update', [
                    'as' => 'frontend.member.account.account_update',
                    'uses' => 'AccountController@accountUpdate'
                ]);

                Route::get('orders', [
                    'as' => 'frontend.member.orders',
                    'uses' => 'AccountController@orders'
                ]);

                Route::get('orders/{reference}/view', [
                    'as' => 'frontend.member.orders.view',
                    'uses' => 'AccountController@viewOrder'
                ]);

                Route::get('address/index', [
                    'as' => 'frontend.member.address.index',
                    'uses' => 'AccountController@addressIndex'
                ]);

                Route::get('address/create', [
                    'as' => 'frontend.member.address.create',
                    'uses' => 'AccountController@addressCreate'
                ]);

                Route::group(['middleware' => 'frontend.customer_can_edit'], function(){
                    Route::get('address/edit/{id}', [
                        'as' => 'frontend.member.address.edit',
                        'uses' => 'AccountController@addressEdit'
                    ]);

                    Route::post('address/save/{id?}', [
                        'as' => 'frontend.member.address.save',
                        'uses' => 'AccountController@addressSave'
                    ]);

                    Route::get('address/default/{id}/{type}', [
                        'as' => 'frontend.member.address.set_default',
                        'uses' => 'AccountController@addressSetDefault'
                    ]);

                    Route::get('address/delete/{id}', [
                        'as' => 'frontend.member.address.delete',
                        'uses' => 'AccountController@addressDelete'
                    ]);
                });

                Route::group(['prefix' => 'reward'], function(){
                    Route::get('points', [
                        'as' => 'frontend.member.reward.points',
                        'uses' => 'AccountController@rewardPoints'
                    ]);

                    Route::post('redeem', [
                        'as' => 'frontend.member.reward.redeem',
                        'uses' => 'AccountController@rewardRedeem'
                    ]);
                });
            });
        });

        //Newsletter widget
        Route::post('member/newsletter_widget/subscribe', [
            'as' => 'frontend.member.newsletter_widget.subscribe',
            'uses' => 'AccountController@newsletterWidgetSubscribe'
        ]);

        //Pages
        Route::get('page/{id}', [
            'as' => 'frontend.page.view',
            'uses' => 'PageController@view'
        ]);

        //Gallery
        Route::get('gallery-category/{id}', [
            'as' => 'frontend.gallery.category.view',
            'uses' => 'GalleryController@viewCategory'
        ]);

        Route::get('gallery/{id}', [
            'as' => 'frontend.gallery.view',
            'uses' => 'GalleryController@viewGallery'
        ]);

        //Posts
        Route::get('post-category/{id}', [
            'as' => 'frontend.post.category.view',
            'uses' => 'PostController@viewCategory'
        ]);

        Route::get('post/{id}', [
            'as' => 'frontend.post.view',
            'uses' => 'PostController@viewPost'
        ]);

        //Catalog
        Route::any('product-categories/{parent_id?}', [
            'as' => 'frontend.catalog.product_categories',
            'uses' => 'CatalogController@productCategories'
        ]);

        Route::any('product-category/{id}', [
            'as' => 'frontend.catalog.product_category.view',
            'uses' => 'CatalogController@viewCategory'
        ]);

        Route::any('product/{id}', [
            'as' => 'frontend.catalog.product.view',
            'uses' => 'CatalogController@viewProduct'
        ]);

        Route::any('composite/{slug}/{product_slug}', [
            'as' => 'frontend.catalog.product.composite.view',
            'uses' => 'ProductCompositeController@viewProduct'
        ]);

        Route::get('sale', [
            'as' => 'frontend.catalog.sale',
            'uses' => 'CatalogController@sale'
        ]);

        Route::get('new-arrival', [
            'as' => 'frontend.catalog.new_arrival',
            'uses' => 'CatalogController@newArrival'
        ]);

        Route::get('search', [
            'as' => 'frontend.catalog.search',
            'uses' => 'CatalogController@search'
        ]);

        Route::get('search/autocomplete', [
            'as' => 'frontend.catalog.search.autocomplete',
            'uses' => 'CatalogController@searchAutocomplete'
        ]);

        Route::get(\Kommercio\Facades\ProjectHelper::getConfig('catalog_options.shop_url'), [
            'as' => 'frontend.catalog.shop',
            'uses' => 'CatalogController@shop'
        ]);

        //Order
        Route::get('cart', [
            'as' => 'frontend.order.cart',
            'uses' => 'OrderController@cart'
        ]);

        Route::get('cart/mini', [
            'as' => 'frontend.order.cart.mini',
            'uses' => 'OrderController@mini_cart'
        ]);

        Route::get('cart/clear', [
            'as' => 'frontend.order.cart.clear',
            'uses' => 'OrderController@cartClear'
        ]);

        Route::post('cart/update', [
            'as' => 'frontend.order.cart.update',
            'uses' => 'OrderController@cartUpdate'
        ]);

        Route::post('order/add-to-cart', [
            'as' => 'frontend.order.add_to_cart',
            'uses' => 'OrderController@addToCart'
        ]);

        Route::get('checkout', [
            'as' => 'frontend.order.checkout',
            'uses' => 'OrderController@checkout'
        ]);

        Route::post('checkout/process', [
            'as' => 'frontend.order.checkout.process',
            'uses' => 'OrderController@checkoutProcess'
        ]);

        Route::get('one-page-checkout', [
            'as' => 'frontend.order.onepage_checkout',
            'uses' => 'OrderController@onePageCheckout'
        ]);

        Route::post('one-page-checkout/process/{type?}', [
            'as' => 'frontend.order.onepage_checkout.process',
            'uses' => 'OrderController@onePageCheckoutProcess'
        ]);

        Route::any('confirm-payment', [
            'as' => 'frontend.order.confirm_payment',
            'uses' => 'OrderController@confirmPayment'
        ]);

        Route::get('checkout/complete', [
            'as' => 'frontend.order.checkout.complete',
            'uses' => 'OrderController@checkoutComplete'
        ]);
    });

    Route::group(['prefix' => config('kommercio.backend_prefix'), 'namespace' => 'Backend', 'middleware' => ['backend.access', 'discern_admin_customer']], function(){
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
            'uses' => 'Auth\AuthController@getLogout'
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

            Route::get('change-store/{id}', [
                'as' => 'backend.change_store',
                'uses' => 'ChamberController@changeStore'
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
                        'uses' => 'CategoryController@index',
                        'permissions' => ['view_product_category']
                    ]);

                    Route::get('create', [
                        'as' => 'backend.catalog.category.create',
                        'uses' => 'CategoryController@create',
                        'permissions' => ['create_product_category']
                    ]);

                    Route::post('store', [
                        'as' => 'backend.catalog.category.store',
                        'uses' => 'CategoryController@store',
                        'permissions' => ['create_product_category']
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.catalog.category.edit',
                        'uses' => 'CategoryController@edit',
                        'permissions' => ['edit_product_category']
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.catalog.category.update',
                        'uses' => 'CategoryController@update',
                        'permissions' => ['edit_product_category']
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.catalog.category.delete',
                        'uses' => 'CategoryController@delete',
                        'permissions' => ['delete_product_category']
                    ]);

                    Route::post('reorder', [
                        'as' => 'backend.catalog.category.reorder',
                        'uses' => 'CategoryController@reorder',
                        'permissions' => ['edit_product_category']
                    ]);

                    Route::get('autocomplete', [
                        'as' => 'backend.catalog.category.autocomplete',
                        'uses' => 'CategoryController@autocomplete',
                        'permissions' => ['view_product_category']
                    ]);
                });

                Route::group(['prefix' => 'product'], function(){
                    Route::any('index', [
                        'as' => 'backend.catalog.product.index',
                        'uses' => 'ProductController@index',
                        'permissions' => ['view_product']
                    ]);

                    Route::get('create', [
                        'as' => 'backend.catalog.product.create',
                        'uses' => 'ProductController@create',
                        'permissions' => ['create_product']
                    ]);

                    Route::post('store', [
                        'as' => 'backend.catalog.product.store',
                        'uses' => 'ProductController@store',
                        'permissions' => ['create_product']
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.catalog.product.edit',
                        'uses' => 'ProductController@edit',
                        'permissions' => ['edit_product']
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.catalog.product.update',
                        'uses' => 'ProductController@update',
                        'permissions' => ['edit_product']
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.catalog.product.delete',
                        'uses' => 'ProductController@delete',
                        'permissions' => ['delete_product']
                    ]);

                    Route::post('{id}/feature/index', [
                        'as' => 'backend.catalog.product.feature_index',
                        'uses' => 'ProductController@featureIndex',
                        'permissions' => ['edit_product']
                    ]);

                    Route::get('{id}/variation/index', [
                        'as' => 'backend.catalog.product.variation_index',
                        'uses' => 'ProductController@variationIndex',
                        'permissions' => ['view_product']
                    ]);

                    Route::post('{id}/variation/form/{variation_id?}', [
                        'as' => 'backend.catalog.product.variation_form',
                        'uses' => 'ProductController@variationForm',
                        'permissions' => ['edit_product']
                    ]);

                    Route::post('{id}/variation/save/{variation_id?}', [
                        'as' => 'backend.catalog.product.variation_save',
                        'uses' => 'ProductController@variationSave',
                        'permissions' => ['edit_product']
                    ]);

                    Route::post('{id}/variation/bulk-form', [
                        'as' => 'backend.catalog.product.variation_bulk_form',
                        'uses' => 'ProductController@variationBulkForm',
                        'permissions' => ['edit_product']
                    ]);

                    Route::post('{id}/variation/bulk-save', [
                        'as' => 'backend.catalog.product.variation_bulk_save',
                        'uses' => 'ProductController@variationBulkSave',
                        'permissions' => ['edit_product']
                    ]);

                    Route::get('autocomplete', [
                        'as' => 'backend.catalog.product.autocomplete',
                        'uses' => 'ProductController@autocomplete',
                        //'permissions' => ['view_product']
                    ]);

                    Route::get('get-related-product/{id?}/{type?}', [
                        'as' => 'backend.catalog.product.get_related',
                        'uses' => 'ProductController@getRelatedProduct',
                        //'permissions' => ['view_product']
                    ]);

                    Route::post('availability/{id}', [
                        'as' => 'backend.catalog.product.availability',
                        'uses' => 'ProductController@availability',
                        //'permissions' => ['view_product']
                    ]);

                    Route::get('{id}/composite/{composite_id}/autocomplete', [
                        'as' => 'backend.catalog.product.composite.autocomplete',
                        'uses' => 'ProductController@compositeAutocomplete',
                        //'permissions' => ['view_product']
                    ]);
                });

                Route::group(['prefix' => 'product-attribute'], function(){
                    Route::any('index', [
                        'as' => 'backend.catalog.product_attribute.index',
                        'uses' => 'ProductAttributeController@index',
                        'permissions' => ['view_product_attribute']
                    ]);

                    Route::get('create', [
                        'as' => 'backend.catalog.product_attribute.create',
                        'uses' => 'ProductAttributeController@create',
                        'permissions' => ['create_product_attribute']
                    ]);

                    Route::post('store', [
                        'as' => 'backend.catalog.product_attribute.store',
                        'uses' => 'ProductAttributeController@store',
                        'permissions' => ['create_product_attribute']
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.catalog.product_attribute.edit',
                        'uses' => 'ProductAttributeController@edit',
                        'permissions' => ['edit_product_attribute']
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.catalog.product_attribute.update',
                        'uses' => 'ProductAttributeController@update',
                        'permissions' => ['edit_product_attribute']
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.catalog.product_attribute.delete',
                        'uses' => 'ProductAttributeController@delete',
                        'permissions' => ['delete_product_attribute']
                    ]);

                    Route::post('reorder', [
                        'as' => 'backend.catalog.product_attribute.reorder',
                        'uses' => 'ProductAttributeController@reorder',
                        'permissions' => ['edit_product_attribute']
                    ]);

                    Route::group(['prefix' => 'value/{attribute_id}', 'permissions' => ['edit_product_attribute']], function(){
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
                        'uses' => 'ProductFeatureController@index',
                        'permissions' => ['view_product_feature']
                    ]);

                    Route::get('create', [
                        'as' => 'backend.catalog.product_feature.create',
                        'uses' => 'ProductFeatureController@create',
                        'permissions' => ['create_product_feature']
                    ]);

                    Route::post('store', [
                        'as' => 'backend.catalog.product_feature.store',
                        'uses' => 'ProductFeatureController@store',
                        'permissions' => ['create_product_feature']
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.catalog.product_feature.edit',
                        'uses' => 'ProductFeatureController@edit',
                        'permissions' => ['edit_product_feature']
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.catalog.product_feature.update',
                        'uses' => 'ProductFeatureController@update',
                        'permissions' => ['edit_product_feature']
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.catalog.product_feature.delete',
                        'uses' => 'ProductFeatureController@delete',
                        'permissions' => ['delete_product_feature']
                    ]);

                    Route::post('reorder', [
                        'as' => 'backend.catalog.product_feature.reorder',
                        'uses' => 'ProductFeatureController@reorder',
                        'permissions' => ['edit_product_feature']
                    ]);

                    Route::group(['prefix' => 'value/{feature_id}', 'permissions' => ['edit_product_feature']], function(){
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

                Route::group(['prefix' => 'product-configuration'], function() {
                    Route::group(['prefix' => 'group'], function(){
                        Route::any('index', [
                            'as' => 'backend.catalog.product_configuration.group.index',
                            'uses' => 'ProductConfigurationGroupController@index',
                            'permissions' => ['view_product_configuration_group']
                        ]);

                        Route::get('create', [
                            'as' => 'backend.catalog.product_configuration.group.create',
                            'uses' => 'ProductConfigurationGroupController@create',
                            'permissions' => ['create_product_configuration_group']
                        ]);

                        Route::post('store', [
                            'as' => 'backend.catalog.product_configuration.group.store',
                            'uses' => 'ProductConfigurationGroupController@store',
                            'permissions' => ['create_product_configuration_group']
                        ]);

                        Route::get('edit/{id}', [
                            'as' => 'backend.catalog.product_configuration.group.edit',
                            'uses' => 'ProductConfigurationGroupController@edit',
                            'permissions' => ['edit_product_configuration_group']
                        ]);

                        Route::post('update/{id}', [
                            'as' => 'backend.catalog.product_configuration.group.update',
                            'uses' => 'ProductConfigurationGroupController@update',
                            'permissions' => ['edit_product_configuration_group']
                        ]);

                        Route::post('delete/{id}', [
                            'as' => 'backend.catalog.product_configuration.group.delete',
                            'uses' => 'ProductConfigurationGroupController@delete',
                            'permissions' => ['delete_product_configuration_group']
                        ]);
                    });

                    Route::get('{group_id}/index', [
                        'as' => 'backend.catalog.product_configuration.index',
                        'uses' => 'ProductConfigurationController@index',
                        'permissions' => ['view_product_configuration_group']
                    ]);

                    Route::get('{group_id}/create', [
                        'as' => 'backend.catalog.product_configuration.create',
                        'uses' => 'ProductConfigurationController@create',
                        'permissions' => ['create_product_configuration_group']
                    ]);

                    Route::post('{group_id}/store', [
                        'as' => 'backend.catalog.product_configuration.store',
                        'uses' => 'ProductConfigurationController@store',
                        'permissions' => ['create_product_configuration_group']
                    ]);

                    Route::get('{group_id}/edit/{id}', [
                        'as' => 'backend.catalog.product_configuration.edit',
                        'uses' => 'ProductConfigurationController@edit',
                        'permissions' => ['edit_product_configuration_group']
                    ]);

                    Route::post('{group_id}/update/{id}', [
                        'as' => 'backend.catalog.product_configuration.update',
                        'uses' => 'ProductConfigurationController@update',
                        'permissions' => ['edit_product_configuration_group']
                    ]);

                    Route::post('{group_id}/delete/{id}', [
                        'as' => 'backend.catalog.product_configuration.delete',
                        'uses' => 'ProductConfigurationController@delete',
                        'permissions' => ['delete_product_configuration_group']
                    ]);

                    Route::post('{group_id}/reorder', [
                        'as' => 'backend.catalog.product_configuration.reorder',
                        'uses' => 'ProductConfigurationController@reorder',
                        'permissions' => ['edit_product_configuration_group']
                    ]);
                });

                Route::group(['prefix' => 'product-composite'], function() {
                    Route::group(['prefix' => 'group'], function(){
                        Route::any('index', [
                            'as' => 'backend.catalog.product_composite.group.index',
                            'uses' => 'ProductCompositeGroupController@index',
                            'permissions' => ['view_product_composite']
                        ]);

                        Route::get('create', [
                            'as' => 'backend.catalog.product_composite.group.create',
                            'uses' => 'ProductCompositeGroupController@create',
                            'permissions' => ['create_product_composite']
                        ]);

                        Route::post('store', [
                            'as' => 'backend.catalog.product_composite.group.store',
                            'uses' => 'ProductCompositeGroupController@store',
                            'permissions' => ['create_product_composite']
                        ]);

                        Route::get('edit/{id}', [
                            'as' => 'backend.catalog.product_composite.group.edit',
                            'uses' => 'ProductCompositeGroupController@edit',
                            'permissions' => ['edit_product_composite']
                        ]);

                        Route::post('update/{id}', [
                            'as' => 'backend.catalog.product_composite.group.update',
                            'uses' => 'ProductCompositeGroupController@update',
                            'permissions' => ['edit_product_composite']
                        ]);

                        Route::post('delete/{id}', [
                            'as' => 'backend.catalog.product_composite.group.delete',
                            'uses' => 'ProductCompositeGroupController@delete',
                            'permissions' => ['delete_product_composite']
                        ]);
                    });

                    Route::get('{group_id}/index', [
                        'as' => 'backend.catalog.product_composite.index',
                        'uses' => 'ProductCompositeController@index',
                        'permissions' => ['view_product_composite']
                    ]);

                    Route::get('{group_id}/create', [
                        'as' => 'backend.catalog.product_composite.create',
                        'uses' => 'ProductCompositeController@create',
                        'permissions' => ['create_product_composite']
                    ]);

                    Route::post('{group_id}/store', [
                        'as' => 'backend.catalog.product_composite.store',
                        'uses' => 'ProductCompositeController@store',
                        'permissions' => ['create_product_composite']
                    ]);

                    Route::get('{group_id}/edit/{id}', [
                        'as' => 'backend.catalog.product_composite.edit',
                        'uses' => 'ProductCompositeController@edit',
                        'permissions' => ['edit_product_composite']
                    ]);

                    Route::post('{group_id}/update/{id}', [
                        'as' => 'backend.catalog.product_composite.update',
                        'uses' => 'ProductCompositeController@update',
                        'permissions' => ['edit_product_composite']
                    ]);

                    Route::post('{group_id}/delete/{id}', [
                        'as' => 'backend.catalog.product_composite.delete',
                        'uses' => 'ProductCompositeController@delete',
                        'permissions' => ['delete_product_composite']
                    ]);

                    Route::post('{group_id}/reorder', [
                        'as' => 'backend.catalog.product_composite.reorder',
                        'uses' => 'ProductCompositeController@reorder',
                        'permissions' => ['edit_product_composite']
                    ]);
                });

                Route::group(['prefix' => 'manufacturer'], function(){
                    Route::get('index', [
                        'as' => 'backend.catalog.manufacturer.index',
                        'uses' => 'ManufacturerController@index',
                        'permissions' => ['view_manufacturer']
                    ]);

                    Route::get('create', [
                        'as' => 'backend.catalog.manufacturer.create',
                        'uses' => 'ManufacturerController@create',
                        'permissions' => ['create_manufacturer']
                    ]);

                    Route::post('store', [
                        'as' => 'backend.catalog.manufacturer.store',
                        'uses' => 'ManufacturerController@store',
                        'permissions' => ['create_manufacturer']
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.catalog.manufacturer.edit',
                        'uses' => 'ManufacturerController@edit',
                        'permissions' => ['edit_manufacturer']
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.catalog.manufacturer.update',
                        'uses' => 'ManufacturerController@update',
                        'permissions' => ['edit_manufacturer']
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.catalog.manufacturer.delete',
                        'uses' => 'ManufacturerController@delete',
                        'permissions' => ['delete_manufacturer']
                    ]);
                });
            });

            //Price Rule
            Route::group(['prefix' => 'price-rule', 'namespace' => 'PriceRule'], function(){
                Route::group(['prefix' => 'product'], function(){
                    Route::get('{product_id}/mini-index', [
                        'as' => 'backend.price_rule.product.mini_index',
                        'uses' => 'ProductPriceRuleController@mini_index',
                        'permissions' => ['view_product_price_rule']
                    ]);

                    Route::post('{product_id}/mini-form/{id?}', [
                        'as' => 'backend.price_rule.product.mini_form',
                        'uses' => 'ProductPriceRuleController@mini_form',
                        'permissions' => ['edit_product_price_rule']
                    ]);

                    Route::post('{product_id}/mini-save/{id?}', [
                        'as' => 'backend.price_rule.product.mini_save',
                        'uses' => 'ProductPriceRuleController@mini_save',
                        'permissions' => ['edit_product_price_rule']
                    ]);

                    Route::get('index', [
                        'as' => 'backend.price_rule.product.index',
                        'uses' => 'ProductPriceRuleController@index',
                        'permissions' => ['view_product_price_rule']
                    ]);

                    Route::get('create', [
                        'as' => 'backend.price_rule.product.create',
                        'uses' => 'ProductPriceRuleController@create',
                        'permissions' => ['create_product_price_rule']
                    ]);

                    Route::post('store', [
                        'as' => 'backend.price_rule.product.store',
                        'uses' => 'ProductPriceRuleController@store',
                        'permissions' => ['create_product_price_rule']
                    ]);

                    Route::get('{id}/edit', [
                        'as' => 'backend.price_rule.product.edit',
                        'uses' => 'ProductPriceRuleController@edit',
                        'permissions' => ['edit_product_price_rule']
                    ]);

                    Route::post('{id}/update', [
                        'as' => 'backend.price_rule.product.update',
                        'uses' => 'ProductPriceRuleController@update',
                        'permissions' => ['edit_product_price_rule']
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.price_rule.product.delete',
                        'uses' => 'ProductPriceRuleController@delete',
                        'permissions' => ['delete_product_price_rule']
                    ]);

                    Route::post('reorder', [
                        'as' => 'backend.price_rule.product.reorder',
                        'uses' => 'ProductPriceRuleController@reorder',
                        'permissions' => ['edit_product_price_rule']
                    ]);
                });

                Route::group(['prefix' => 'cart'], function(){
                    Route::get('index', [
                        'as' => 'backend.price_rule.cart.index',
                        'uses' => 'CartPriceRuleController@index',
                        'permissions' => ['view_cart_price_rule']
                    ]);

                    Route::get('create', [
                        'as' => 'backend.price_rule.cart.create',
                        'uses' => 'CartPriceRuleController@create',
                        'permissions' => ['create_cart_price_rule']
                    ]);

                    Route::post('store', [
                        'as' => 'backend.price_rule.cart.store',
                        'uses' => 'CartPriceRuleController@store',
                        'permissions' => ['create_cart_price_rule']
                    ]);

                    Route::get('{id}/edit', [
                        'as' => 'backend.price_rule.cart.edit',
                        'uses' => 'CartPriceRuleController@edit',
                        'permissions' => ['edit_cart_price_rule']
                    ]);

                    Route::post('{id}/update', [
                        'as' => 'backend.price_rule.cart.update',
                        'uses' => 'CartPriceRuleController@update',
                        'permissions' => ['edit_cart_price_rule']
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.price_rule.cart.delete',
                        'uses' => 'CartPriceRuleController@delete',
                        'permissions' => ['delete_cart_price_rule']
                    ]);

                    Route::post('reorder', [
                        'as' => 'backend.price_rule.cart.reorder',
                        'uses' => 'CartPriceRuleController@reorder',
                        'permissions' => ['edit_cart_price_rule']
                    ]);

                    Route::get('{cart_price_rule_id}/coupons/index', [
                        'as' => 'backend.price_rule.cart.coupon.index',
                        'uses' => 'CartPriceRuleController@couponIndex',
                        'permissions' => ['view_cart_price_rule']
                    ]);

                    Route::get('{cart_price_rule_id}/coupons/form/{id?}', [
                        'as' => 'backend.price_rule.cart.coupon.form',
                        'uses' => 'CartPriceRuleController@couponForm',
                        'permissions' => ['create_cart_price_rule', 'edit_cart_price_rule']
                    ]);

                    Route::post('{cart_price_rule_id}/coupons/save', [
                        'as' => 'backend.price_rule.cart.coupon.save',
                        'uses' => 'CartPriceRuleController@couponSave',
                        'permissions' => ['create_cart_price_rule', 'edit_cart_price_rule']
                    ]);

                    Route::post('{cart_price_rule_id}/coupons/delete/{id}', [
                        'as' => 'backend.price_rule.cart.coupon.delete',
                        'uses' => 'CartPriceRuleController@couponDelete',
                        'permissions' => ['delete_cart_price_rule']
                    ]);
                });
            });

            //Order
            Route::group(['prefix' => 'sales', 'namespace' => 'Sales'], function(){
                Route::group(['prefix' => 'order'], function(){
                    Route::any('index', [
                        'as' => 'backend.sales.order.index',
                        'uses' => 'OrderController@index',
                        'permissions' => ['view_order']
                    ]);

                    Route::get('create', [
                        'as' => 'backend.sales.order.create',
                        'uses' => 'OrderController@create',
                        'permissions' => ['create_order']
                    ]);

                    Route::post('store', [
                        'as' => 'backend.sales.order.store',
                        'uses' => 'OrderController@store',
                        'permissions' => ['create_order']
                    ]);

                    Route::get('view/{id}', [
                        'as' => 'backend.sales.order.view',
                        'uses' => 'OrderController@view',
                        'permissions' => ['view_order']
                    ]);

                    Route::get('quick-content-view/{id}', [
                        'as' => 'backend.sales.order.quick_content_view',
                        'uses' => 'OrderController@quickContentView',
                        'permissions' => ['view_order']
                    ]);

                    Route::get('quick-payment-view/{id}', [
                        'as' => 'backend.sales.order.quick_payment_view',
                        'uses' => 'OrderController@quickPaymentView',
                        'permissions' => ['view_payment']
                    ]);

                    Route::get('print/{id}/{type?}', [
                        'as' => 'backend.sales.order.print',
                        'uses' => 'OrderController@printOrder',
                        'permissions' => ['view_order']
                    ]);

                    Route::get('delete/all', [
                        'as' => 'backend.sales.order.delete_all',
                        'uses' => 'OrderController@deleteAll',
                        'permissions' => ['delete_order']
                    ]);

                    Route::group(['middleware' => ['backend.order_editable']], function(){
                        Route::get('edit/{id}', [
                            'as' => 'backend.sales.order.edit',
                            'uses' => 'OrderController@edit',
                            'permissions' => ['edit_order']
                        ]);

                        Route::post('update/{id}', [
                            'as' => 'backend.sales.order.update',
                            'uses' => 'OrderController@update',
                            'permissions' => ['edit_order']
                        ]);

                        Route::post('delete/{id}', [
                            'as' => 'backend.sales.order.delete',
                            'uses' => 'OrderController@delete',
                            'middleware' => ['backend.order_deleteable'],
                            'permissions' => ['delete_order']
                        ]);
                    });

                    Route::post('action/bulk', [
                        'as' => 'backend.sales.order.bulk_action',
                        'uses' => 'OrderController@bulkAction',
                    ]);

                    Route::any('process/{action}/{id?}', [
                        'as' => 'backend.sales.order.process',
                        'uses' => 'OrderController@process',
                        'permissions' => [['place_order', 'process_order', 'ship_order', 'complete_order', 'cancel_order']]
                    ]);

                    Route::any('resend-email/{action}/{id?}', [
                        'as' => 'backend.sales.order.resend_email',
                        'uses' => 'OrderController@resendEmail',
                        'permissions' => [['resend_order_email', 'place_order', 'process_order', 'ship_order', 'complete_order', 'cancel_order']]
                    ]);

                    Route::post('copy/customer_information/{type}/{profile_id?}', [
                        'as' => 'backend.sales.order.copy_customer_information',
                        'uses' => 'OrderController@copyCustomerInformation',
                        'permissions' => [['create_order', 'edit_order']]
                    ]);

                    Route::post('line_item/{type}/row/{id?}', [
                        'as' => 'backend.sales.order.line_item.row',
                        'uses' => 'OrderController@lineItemRow',
                        'permissions' => [['create_order', 'edit_order']]
                    ]);

                    Route::any('shipping/options', [
                        'as' => 'backend.sales.order.shipping_options',
                        'uses' => 'OrderController@shippingOptions',
                        'permissions' => [['create_order', 'edit_order']]
                    ]);

                    Route::post('order-cart-rules/get', [
                        'as' => 'backend.sales.order.get_cart_rules',
                        'uses' => 'OrderController@getCartRules',
                        'permissions' => [['create_order', 'edit_order']]
                    ]);

                    Route::post('coupon/add', [
                        'as' => 'backend.sales.order.add_coupon',
                        'uses' => 'OrderController@addCoupon',
                        'permissions' => [['create_order', 'edit_order']]
                    ]);

                    Route::post('coupon/{id}/remove', [
                        'as' => 'backend.sales.order.remove_coupon',
                        'uses' => 'OrderController@removeCoupon',
                        'permissions' => [['create_order', 'edit_order']]
                    ]);

                    Route::group(['prefix' => 'payment'], function(){
                        Route::get('{order_id}/index', [
                            'as' => 'backend.sales.order.payment.index',
                            'uses' => 'PaymentController@orderPaymentIndex',
                            'permissions' => ['view_payment']
                        ]);

                        Route::get('{order_id}/form', [
                            'as' => 'backend.sales.order.payment.form',
                            'uses' => 'PaymentController@orderPaymentForm',
                            'permissions' => ['create_payment']
                        ]);

                        Route::post('{order_id}/save', [
                            'as' => 'backend.sales.order.payment.save',
                            'uses' => 'PaymentController@orderPaymentSave',
                            'permissions' => ['create_payment']
                        ]);

                        Route::any('process/{action}/{id}', [
                            'as' => 'backend.sales.order.payment.process',
                            'uses' => 'PaymentController@process',
                            'permissions' => [['confirm_payment', 'void_payment']]
                        ]);
                    });

                    Route::group(['prefix' => 'internal-memo'], function(){
                        Route::get('{order_id}/index', [
                            'as' => 'backend.sales.order.internal_memo.index',
                            'uses' => 'OrderCommentController@orderCommentIndex',
                            'permissions' => ['view_order_internal_memo']
                        ]);

                        Route::get('{order_id}/form', [
                            'as' => 'backend.sales.order.internal_memo.form',
                            'uses' => 'OrderCommentController@orderCommentForm',
                            'permissions' => ['create_order_internal_memo']
                        ]);

                        Route::post('{order_id}/save', [
                            'as' => 'backend.sales.order.internal_memo.save',
                            'uses' => 'OrderCommentController@orderCommentSave',
                            'permissions' => ['create_order_internal_memo']
                        ]);
                    });
                });

                //Order Limits
                Route::group(['prefix' => 'order-limit'], function(){
                    Route::get('{type}/index', [
                        'as' => 'backend.order_limit.index',
                        'uses' => 'OrderLimitController@index',
                        'permissions' => ['view_order_limit']
                    ]);

                    Route::get('{type}/create', [
                        'as' => 'backend.order_limit.create',
                        'uses' => 'OrderLimitController@create',
                        'permissions' => ['create_order_limit']
                    ]);

                    Route::post('{type}/store', [
                        'as' => 'backend.order_limit.store',
                        'uses' => 'OrderLimitController@store',
                        'permissions' => ['create_order_limit']
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.order_limit.edit',
                        'uses' => 'OrderLimitController@edit',
                        'permissions' => ['edit_order_limit']
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.order_limit.update',
                        'uses' => 'OrderLimitController@update',
                        'permissions' => ['edit_order_limit']
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.order_limit.delete',
                        'uses' => 'OrderLimitController@delete',
                        'permissions' => ['delete_order_limit']
                    ]);

                    Route::post('{type}/reorder', [
                        'as' => 'backend.order_limit.reorder',
                        'uses' => 'OrderLimitController@reorder',
                        'permissions' => ['edit_order_limit']
                    ]);
                });
            });

            //Configurations
            Route::group(['prefix' => 'configuration'], function(){
                //Store
                Route::group(['prefix' => 'store', 'namespace' => 'Store'], function(){
                    Route::get('index', [
                        'as' => 'backend.store.index',
                        'uses' => 'StoreController@index',
                        'permissions' => ['view_store']
                    ]);

                    Route::get('create', [
                        'as' => 'backend.store.create',
                        'uses' => 'StoreController@create',
                        'permissions' => ['create_store']
                    ]);

                    Route::post('store', [
                        'as' => 'backend.store.store',
                        'uses' => 'StoreController@store',
                        'permissions' => ['create_store']
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.store.edit',
                        'uses' => 'StoreController@edit',
                        'permissions' => ['edit_store']
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.store.update',
                        'uses' => 'StoreController@update',
                        'permissions' => ['edit_store']
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.store.delete',
                        'uses' => 'StoreController@delete',
                        'permissions' => ['delete_store']
                    ]);
                });

                //Payment Methods
                Route::group(['prefix' => 'payment-method', 'namespace' => 'PaymentMethod'], function(){
                    Route::get('index', [
                        'as' => 'backend.payment_method.index',
                        'uses' => 'PaymentMethodController@index',
                        'permissions' => ['view_payment_method']
                    ]);

                    Route::get('create', [
                        'as' => 'backend.payment_method.create',
                        'uses' => 'PaymentMethodController@create',
                        'permissions' => ['create_payment_method']
                    ]);

                    Route::post('store', [
                        'as' => 'backend.payment_method.store',
                        'uses' => 'PaymentMethodController@store',
                        'permissions' => ['create_payment_method']
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.payment_method.edit',
                        'uses' => 'PaymentMethodController@edit',
                        'permissions' => ['edit_payment_method']
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.payment_method.update',
                        'uses' => 'PaymentMethodController@update',
                        'permissions' => ['edit_payment_method']
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.payment_method.delete',
                        'uses' => 'PaymentMethodController@delete',
                        'permissions' => ['delete_payment_method']
                    ]);

                    Route::post('reorder', [
                        'as' => 'backend.payment_method.reorder',
                        'uses' => 'PaymentMethodController@reorder',
                        'permissions' => ['edit_payment_method']
                    ]);
                });

                //Shipping Methods
                Route::group(['prefix' => 'shipping-method', 'namespace' => 'ShippingMethod'], function(){
                    Route::get('index', [
                        'as' => 'backend.shipping_method.index',
                        'uses' => 'ShippingMethodController@index',
                        'permissions' => ['view_shipping_method']
                    ]);

                    Route::get('create', [
                        'as' => 'backend.shipping_method.create',
                        'uses' => 'ShippingMethodController@create',
                        'permissions' => ['create_shipping_method']
                    ]);

                    Route::post('store', [
                        'as' => 'backend.shipping_method.store',
                        'uses' => 'ShippingMethodController@store',
                        'permissions' => ['create_shipping_method']
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.shipping_method.edit',
                        'uses' => 'ShippingMethodController@edit',
                        'permissions' => ['edit_shipping_method']
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.shipping_method.update',
                        'uses' => 'ShippingMethodController@update',
                        'permissions' => ['edit_shipping_method']
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.shipping_method.delete',
                        'uses' => 'ShippingMethodController@delete',
                        'permissions' => ['delete_shipping_method']
                    ]);

                    Route::post('reorder', [
                        'as' => 'backend.shipping_method.reorder',
                        'uses' => 'ShippingMethodController@reorder',
                        'permissions' => ['edit_shipping_method']
                    ]);
                });

                //Address
                Route::group(['prefix' => 'address', 'namespace' => 'Address'], function(){
                    Route::any('{type}/index', [
                        'as' => 'backend.configuration.address.index',
                        'uses' => 'AddressController@index',
                        'permissions' => ['view_address']
                    ]);

                    Route::get('{type}/create', [
                        'as' => 'backend.configuration.address.create',
                        'uses' => 'AddressController@create',
                        'permissions' => ['create_address']
                    ]);

                    Route::post('{type}/store', [
                        'as' => 'backend.configuration.address.store',
                        'uses' => 'AddressController@store',
                        'permissions' => ['create_address']
                    ]);

                    Route::get('{type}/edit/{id}', [
                        'as' => 'backend.configuration.address.edit',
                        'uses' => 'AddressController@edit',
                        'permissions' => ['edit_address']
                    ]);

                    Route::post('{type}/update/{id}', [
                        'as' => 'backend.configuration.address.update',
                        'uses' => 'AddressController@update',
                        'permissions' => ['edit_address']
                    ]);

                    Route::post('{type}/delete/{id}', [
                        'as' => 'backend.configuration.address.delete',
                        'uses' => 'AddressController@delete',
                        'permissions' => ['delete_address']
                    ]);

                    Route::post('{type}/reorder', [
                        'as' => 'backend.configuration.address.reorder',
                        'uses' => 'AddressController@reorder',
                        'permissions' => ['edit_address']
                    ]);

                    Route::any('import/{type}/{id}', [
                        'as' => 'backend.configuration.address.import',
                        'uses' => 'AddressController@import',
                        'permissions' => ['edit_address']
                    ]);

                    Route::any('{type}/import-all', [
                        'as' => 'backend.configuration.address.import_all',
                        'uses' => 'AddressController@importAll',
                        'permissions' => ['edit_address']
                    ]);

                    Route::any('rates/{type}/{id}', [
                        'as' => 'backend.configuration.address.rates',
                        'uses' => 'AddressController@rates',
                        'permissions' => ['edit_address']
                    ]);
                });

                //Taxes
                Route::group(['prefix' => 'tax', 'namespace' => 'Tax'], function(){
                    Route::get('index', [
                        'as' => 'backend.tax.index',
                        'uses' => 'TaxController@index',
                        'permissions' => ['view_tax']
                    ]);

                    Route::get('create', [
                        'as' => 'backend.tax.create',
                        'uses' => 'TaxController@create',
                        'permissions' => ['create_tax']
                    ]);

                    Route::post('store', [
                        'as' => 'backend.tax.store',
                        'uses' => 'TaxController@store',
                        'permissions' => ['create_tax']
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.tax.edit',
                        'uses' => 'TaxController@edit',
                        'permissions' => ['edit_tax']
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.tax.update',
                        'uses' => 'TaxController@update',
                        'permissions' => ['edit_tax']
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.tax.delete',
                        'uses' => 'TaxController@delete',
                        'permissions' => ['delete_tax']
                    ]);

                    Route::post('reorder', [
                        'as' => 'backend.tax.reorder',
                        'uses' => 'TaxController@reorder',
                        'permissions' => ['edit_tax']
                    ]);

                    Route::get('get', [
                        'as' => 'backend.tax.get',
                        'uses' => 'TaxController@get',
                        'permissions' => [['create_order', 'edit_order']]
                    ]);

                    Route::get('country_children/{country_id?}', [
                        'as' => 'backend.tax.country_children',
                        'uses' => 'TaxController@countryChildren',
                        'permissions' => [['create_tax', 'edit_tax']]
                    ]);
                });

                //Warehouse
                Route::group(['prefix' => 'warehouse', 'namespace' => 'Warehouse'], function(){
                    Route::get('index', [
                        'as' => 'backend.warehouse.index',
                        'uses' => 'WarehouseController@index',
                        'permissions' => ['view_warehouse']
                    ]);

                    Route::get('create', [
                        'as' => 'backend.warehouse.create',
                        'uses' => 'WarehouseController@create',
                        'permissions' => ['create_warehouse']
                    ]);

                    Route::post('store', [
                        'as' => 'backend.warehouse.store',
                        'uses' => 'WarehouseController@store',
                        'permissions' => ['create_warehouse']
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.warehouse.edit',
                        'uses' => 'WarehouseController@edit',
                        'permissions' => ['edit_warehouse']
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.warehouse.update',
                        'uses' => 'WarehouseController@update',
                        'permissions' => ['edit_warehouse']
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.warehouse.delete',
                        'uses' => 'WarehouseController@delete',
                        'permissions' => ['delete_warehouse']
                    ]);
                });
            });

            //Utility
            Route::group(['prefix' => 'utility', 'namespace' => 'Utility'], function(){
                Route::group(['prefix' => 'import'], function(){
                    Route::any('manufacturer', [
                        'as' => 'backend.utility.import.manufacturer',
                        'uses' => 'ImportController@manufacturer',
                        'permissions' => ['import_manufacturer']
                    ]);

                    Route::any('product-attribute', [
                        'as' => 'backend.utility.import.product_attribute',
                        'uses' => 'ImportController@productAttribute',
                        'permissions' => ['import_product_attribute']
                    ]);

                    Route::any('product', [
                        'as' => 'backend.utility.import.product',
                        'uses' => 'ImportController@product',
                        'permissions' => ['import_product']
                    ]);
                });
            });

            //Customers
            Route::group(['prefix' => 'customer', 'namespace' => 'Customer'], function(){
                Route::any('index', [
                    'as' => 'backend.customer.index',
                    'uses' => 'CustomerController@index',
                    'permissions' => ['view_customer']
                ]);

                Route::get('autocomplete', [
                    'as' => 'backend.customer.autocomplete',
                    'uses' => 'CustomerController@autocomplete',
                ]);

                Route::get('create', [
                    'as' => 'backend.customer.create',
                    'uses' => 'CustomerController@create',
                    'permissions' => ['create_customer']
                ]);

                Route::post('store', [
                    'as' => 'backend.customer.store',
                    'uses' => 'CustomerController@store',
                    'permissions' => ['create_customer']
                ]);

                Route::get('edit/{id}', [
                    'as' => 'backend.customer.edit',
                    'uses' => 'CustomerController@edit',
                    'permissions' => ['edit_customer']
                ]);

                Route::post('update/{id}', [
                    'as' => 'backend.customer.update',
                    'uses' => 'CustomerController@update',
                    'permissions' => ['edit_customer']
                ]);

                Route::get('view/{id}', [
                    'as' => 'backend.customer.view',
                    'uses' => 'CustomerController@view',
                    'permissions' => ['view_customer']
                ]);

                Route::post('delete/{id}', [
                    'as' => 'backend.customer.delete',
                    'uses' => 'CustomerController@delete',
                    'permissions' => ['delete_customer']
                ]);

                Route::get('{customer_id}/address/index', [
                    'as' => 'backend.customer.address.index',
                    'uses' => 'CustomerController@addressIndex',
                    'permissions' => ['view_customer']
                ]);

                Route::get('{customer_id}/address/form/{id?}', [
                    'as' => 'backend.customer.address.form',
                    'uses' => 'CustomerController@addressForm',
                    'permissions' => ['edit_customer']
                ]);

                Route::post('{customer_id}/address/save/{id?}', [
                    'as' => 'backend.customer.address.save',
                    'uses' => 'CustomerController@addressSave',
                    'permissions' => ['edit_customer']
                ]);

                Route::post('{customer_id}/address/delete/{id}', [
                    'as' => 'backend.customer.address.delete',
                    'uses' => 'CustomerController@addressDelete',
                    'permissions' => ['edit_customer']
                ]);

                Route::get('{customer_id}/reward-point/form/{type}', [
                    'as' => 'backend.customer.reward_point.mini_form',
                    'uses' => 'CustomerController@rewardPointForm',
                    'permissions' => ['add_reward_points']
                ]);

                Route::get('{customer_id}/reward-point/index', [
                    'as' => 'backend.customer.reward_point.mini_index',
                    'uses' => 'CustomerController@rewardPointIndex',
                    'permissions' => ['view_reward_points']
                ]);

                Route::post('{customer_id}/reward-point/save', [
                    'as' => 'backend.customer.reward_point.mini_save',
                    'uses' => 'CustomerController@rewardPointSave',
                    'permissions' => ['add_reward_points']
                ]);

                Route::post('{customer_id}/redeem', [
                    'as' => 'backend.customer.reward_point.redeem',
                    'uses' => 'CustomerController@redeem',
                    'permissions' => ['create_redemptions']
                ]);

                Route::group(['prefix' => 'reward-point'], function(){
                    Route::any('index', [
                        'as' => 'backend.customer.reward_point.index',
                        'uses' => 'RewardPointController@index',
                        'permissions' => ['view_reward_points']
                    ]);

                    Route::post('{id}/approve', [
                        'as' => 'backend.customer.reward_point.approve',
                        'uses' => 'RewardPointController@approve',
                        'permissions' => ['approve_reward_points']
                    ]);

                    Route::post('{id}/reject', [
                        'as' => 'backend.customer.reward_point.reject',
                        'uses' => 'RewardPointController@reject',
                        'permissions' => ['reject_reward_points']
                    ]);
                });

                //Reward Rules
                Route::group(['prefix' => 'reward-rule'], function(){
                    Route::get('index', [
                        'as' => 'backend.customer.reward_rule.index',
                        'uses' => 'RewardRuleController@index',
                        'permissions' => ['view_reward_points_rules']
                    ]);

                    Route::get('create', [
                        'as' => 'backend.customer.reward_rule.create',
                        'uses' => 'RewardRuleController@create',
                        'permissions' => ['create_reward_points_rules']
                    ]);

                    Route::post('store', [
                        'as' => 'backend.customer.reward_rule.store',
                        'uses' => 'RewardRuleController@store',
                        'permissions' => ['create_reward_points_rules']
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.customer.reward_rule.edit',
                        'uses' => 'RewardRuleController@edit',
                        'permissions' => ['edit_reward_points_rules']
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.customer.reward_rule.update',
                        'uses' => 'RewardRuleController@update',
                        'permissions' => ['edit_reward_points_rules']
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.customer.reward_rule.delete',
                        'uses' => 'RewardRuleController@delete',
                        'permissions' => ['delete_reward_points_rules']
                    ]);

                    Route::post('reorder', [
                        'as' => 'backend.customer.reward_rule.reorder',
                        'uses' => 'RewardRuleController@reorder',
                        'permissions' => ['edit_reward_points_rules']
                    ]);

                    Route::get('get', [
                        'as' => 'backend.customer.reward_rule.get',
                        'uses' => 'RewardRuleController@get',
                    ]);
                });

                //Rewards
                Route::group(['prefix' => 'rewards'], function(){
                    Route::get('index', [
                        'as' => 'backend.customer.reward.index',
                        'uses' => 'RewardController@index',
                        'permissions' => ['view_reward']
                    ]);

                    Route::get('create', [
                        'as' => 'backend.customer.reward.create',
                        'uses' => 'RewardController@create',
                        'permissions' => ['create_reward']
                    ]);

                    Route::post('store', [
                        'as' => 'backend.customer.reward.store',
                        'uses' => 'RewardController@store',
                        'permissions' => ['create_reward']
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.customer.reward.edit',
                        'uses' => 'RewardController@edit',
                        'permissions' => ['edit_reward']
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.customer.reward.update',
                        'uses' => 'RewardController@update',
                        'permissions' => ['edit_reward']
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.customer.reward.delete',
                        'uses' => 'RewardController@delete',
                        'permissions' => ['delete_reward']
                    ]);
                });

                //Redemptions
                Route::group(['prefix' => 'redemptions'], function(){
                    Route::any('index', [
                        'as' => 'backend.customer.redemption.index',
                        'uses' => 'RedemptionController@index',
                        'permissions' => ['view_redemptions']
                    ]);

                    Route::post('{id}/mark/used', [
                        'as' => 'backend.customer.redemption.mark_used',
                        'uses' => 'RedemptionController@markUsed',
                        'permissions' => ['mark_used_redemptions']
                    ]);
                });

                //Customer Group
                Route::group(['prefix' => 'group'], function(){
                    Route::get('index', [
                        'as' => 'backend.customer.group.index',
                        'uses' => 'CustomerGroupController@index',
                        'permissions' => ['view_customer_group']
                    ]);

                    Route::get('create', [
                        'as' => 'backend.customer.group.create',
                        'uses' => 'CustomerGroupController@create',
                        'permissions' => ['create_customer_group']
                    ]);

                    Route::post('store', [
                        'as' => 'backend.customer.group.store',
                        'uses' => 'CustomerGroupController@store',
                        'permissions' => ['create_customer_group']
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.customer.group.edit',
                        'uses' => 'CustomerGroupController@edit',
                        'permissions' => ['edit_customer_group']
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.customer.group.update',
                        'uses' => 'CustomerGroupController@update',
                        'permissions' => ['edit_customer_group']
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.customer.group.delete',
                        'uses' => 'CustomerGroupController@delete',
                        'permissions' => ['delete_customer_group']
                    ]);

                    Route::post('reorder', [
                        'as' => 'backend.customer.group.reorder',
                        'uses' => 'CustomerGroupController@reorder',
                        'permissions' => ['edit_customer_group']
                    ]);
                });
            });

            //CMS
            Route::group(['namespace' => 'CMS', 'prefix' => 'cms'], function(){
                //Banner
                Route::group(['prefix' => 'banner'], function() {
                    Route::get('group/index', [
                        'as' => 'backend.cms.banner_group.index',
                        'uses' => 'BannerGroupController@index',
                        'permissions' => ['view_banner']
                    ]);

                    Route::get('group/create', [
                        'as' => 'backend.cms.banner_group.create',
                        'uses' => 'BannerGroupController@create',
                        'permissions' => ['create_banner_group']
                    ]);

                    Route::post('group/store', [
                        'as' => 'backend.cms.banner_group.store',
                        'uses' => 'BannerGroupController@store',
                        'permissions' => ['create_banner_group']
                    ]);

                    Route::get('group/edit/{id}', [
                        'as' => 'backend.cms.banner_group.edit',
                        'uses' => 'BannerGroupController@edit',
                        'permissions' => ['edit_banner_group']
                    ]);

                    Route::post('group/update/{id}', [
                        'as' => 'backend.cms.banner_group.update',
                        'uses' => 'BannerGroupController@update',
                        'permissions' => ['edit_banner_group']
                    ]);

                    Route::post('group/delete/{id}', [
                        'as' => 'backend.cms.banner_group.delete',
                        'uses' => 'BannerGroupController@delete',
                        'permissions' => ['delete_banner_group']
                    ]);

                    //Banners
                    Route::get('{banner_group_id}/index', [
                        'as' => 'backend.cms.banner.index',
                        'uses' => 'BannerController@index',
                        'permissions' => ['view_banner']
                    ]);

                    Route::get('create', [
                        'as' => 'backend.cms.banner.create',
                        'uses' => 'BannerController@create',
                        'permissions' => ['create_banner']
                    ]);

                    Route::post('store', [
                        'as' => 'backend.cms.banner.store',
                        'uses' => 'BannerController@store',
                        'permissions' => ['create_banner']
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.cms.banner.edit',
                        'uses' => 'BannerController@edit',
                        'permissions' => ['edit_banner']
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.cms.banner.update',
                        'uses' => 'BannerController@update',
                        'permissions' => ['edit_banner']
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.cms.banner.delete',
                        'uses' => 'BannerController@delete',
                        'permissions' => ['delete_banner']
                    ]);

                    Route::post('reorder', [
                        'as' => 'backend.cms.banner.reorder',
                        'uses' => 'BannerController@reorder',
                        'permissions' => ['edit_banner']
                    ]);
                });

                //Page
                Route::group(['prefix' => 'page'], function(){
                    Route::get('index', [
                        'as' => 'backend.cms.page.index',
                        'uses' => 'PageController@index',
                        'permissions' => ['view_page']
                    ]);

                    Route::get('create', [
                        'as' => 'backend.cms.page.create',
                        'uses' => 'PageController@create',
                        'permissions' => ['create_page']
                    ]);

                    Route::post('store', [
                        'as' => 'backend.cms.page.store',
                        'uses' => 'PageController@store',
                        'permissions' => ['create_page']
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.cms.page.edit',
                        'uses' => 'PageController@edit',
                        'permissions' => ['edit_page']
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.cms.page.update',
                        'uses' => 'PageController@update',
                        'permissions' => ['edit_page']
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.cms.page.delete',
                        'uses' => 'PageController@delete',
                        'permissions' => ['delete_page']
                    ]);
                });

                //Gallery
                Route::group(['prefix' => 'gallery'], function(){
                    Route::group(['prefix' => 'category'], function(){
                        Route::get('index/{parent?}', [
                            'as' => 'backend.cms.gallery.category.index',
                            'uses' => 'GalleryCategoryController@index',
                            'permissions' => ['view_gallery_category']
                        ]);

                        Route::get('create', [
                            'as' => 'backend.cms.gallery.category.create',
                            'uses' => 'GalleryCategoryController@create',
                            'permissions' => ['create_gallery_category']
                        ]);

                        Route::post('store', [
                            'as' => 'backend.cms.gallery.category.store',
                            'uses' => 'GalleryCategoryController@store',
                            'permissions' => ['create_gallery_category']
                        ]);

                        Route::get('edit/{id}', [
                            'as' => 'backend.cms.gallery.category.edit',
                            'uses' => 'GalleryCategoryController@edit',
                            'permissions' => ['edit_gallery_category']
                        ]);

                        Route::post('update/{id}', [
                            'as' => 'backend.cms.gallery.category.update',
                            'uses' => 'GalleryCategoryController@update',
                            'permissions' => ['edit_gallery_category']
                        ]);

                        Route::post('delete/{id}', [
                            'as' => 'backend.cms.gallery.category.delete',
                            'uses' => 'GalleryCategoryController@delete',
                            'permissions' => ['delete_gallery_category']
                        ]);

                        Route::post('reorder', [
                            'as' => 'backend.cms.gallery.category.reorder',
                            'uses' => 'GalleryCategoryController@reorder',
                            'permissions' => ['edit_gallery_category']
                        ]);
                    });

                    Route::any('index', [
                        'as' => 'backend.cms.gallery.index',
                        'uses' => 'GalleryController@index',
                        'permissions' => ['view_gallery']
                    ]);

                    Route::get('create', [
                        'as' => 'backend.cms.gallery.create',
                        'uses' => 'GalleryController@create',
                        'permissions' => ['create_gallery']
                    ]);

                    Route::post('store', [
                        'as' => 'backend.cms.gallery.store',
                        'uses' => 'GalleryController@store',
                        'permissions' => ['create_gallery']
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.cms.gallery.edit',
                        'uses' => 'GalleryController@edit',
                        'permissions' => ['edit_gallery']
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.cms.gallery.update',
                        'uses' => 'GalleryController@update',
                        'permissions' => ['edit_gallery']
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.cms.gallery.delete',
                        'uses' => 'GalleryController@delete',
                        'permissions' => ['delete_gallery']
                    ]);
                });

                //Post
                Route::group(['prefix' => 'post'], function(){
                    Route::group(['prefix' => 'category'], function(){
                        Route::get('index/{parent?}', [
                            'as' => 'backend.cms.post.category.index',
                            'uses' => 'PostCategoryController@index',
                            'permissions' => ['view_post_category']
                        ]);

                        Route::get('create', [
                            'as' => 'backend.cms.post.category.create',
                            'uses' => 'PostCategoryController@create',
                            'permissions' => ['create_post_category']
                        ]);

                        Route::post('store', [
                            'as' => 'backend.cms.post.category.store',
                            'uses' => 'PostCategoryController@store',
                            'permissions' => ['create_post_category']
                        ]);

                        Route::get('edit/{id}', [
                            'as' => 'backend.cms.post.category.edit',
                            'uses' => 'PostCategoryController@edit',
                            'permissions' => ['edit_post_category']
                        ]);

                        Route::post('update/{id}', [
                            'as' => 'backend.cms.post.category.update',
                            'uses' => 'PostCategoryController@update',
                            'permissions' => ['edit_post_category']
                        ]);

                        Route::post('delete/{id}', [
                            'as' => 'backend.cms.post.category.delete',
                            'uses' => 'PostCategoryController@delete',
                            'permissions' => ['delete_post_category']
                        ]);

                        Route::post('reorder', [
                            'as' => 'backend.cms.post.category.reorder',
                            'uses' => 'PostCategoryController@reorder',
                            'permissions' => ['edit_post_category']
                        ]);
                    });

                    Route::any('index', [
                        'as' => 'backend.cms.post.index',
                        'uses' => 'PostController@index',
                        'permissions' => ['view_post']
                    ]);

                    Route::get('create', [
                        'as' => 'backend.cms.post.create',
                        'uses' => 'PostController@create',
                        'permissions' => ['create_post']
                    ]);

                    Route::post('store', [
                        'as' => 'backend.cms.post.store',
                        'uses' => 'PostController@store',
                        'permissions' => ['create_post']
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.cms.post.edit',
                        'uses' => 'PostController@edit',
                        'permissions' => ['edit_post']
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.cms.post.update',
                        'uses' => 'PostController@update',
                        'permissions' => ['edit_post']
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.cms.post.delete',
                        'uses' => 'PostController@delete',
                        'permissions' => ['delete_post']
                    ]);
                });

                //Block
                Route::group(['prefix' => 'block'], function(){
                    Route::get('index', [
                        'as' => 'backend.cms.block.index',
                        'uses' => 'BlockController@index',
                        'permissions' => ['delete_block']
                    ]);

                    Route::get('create', [
                        'as' => 'backend.cms.block.create',
                        'uses' => 'BlockController@create',
                        'permissions' => ['delete_block']
                    ]);

                    Route::post('store', [
                        'as' => 'backend.cms.block.store',
                        'uses' => 'BlockController@store',
                        'permissions' => ['delete_block']
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.cms.block.edit',
                        'uses' => 'BlockController@edit',
                        'permissions' => ['delete_block']
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.cms.block.update',
                        'uses' => 'BlockController@update',
                        'permissions' => ['delete_block']
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.cms.block.delete',
                        'uses' => 'BlockController@delete',
                        'permissions' => ['delete_block']
                    ]);
                });

                //Menu
                Route::group(['prefix' => 'menu'], function(){
                    Route::get('index', [
                        'as' => 'backend.cms.menu.index',
                        'uses' => 'MenuController@index',
                        'permissions' => ['view_menu']
                    ]);

                    Route::get('create', [
                        'as' => 'backend.cms.menu.create',
                        'uses' => 'MenuController@create',
                        'permissions' => ['create_menu']
                    ]);

                    Route::post('store', [
                        'as' => 'backend.cms.menu.store',
                        'uses' => 'MenuController@store',
                        'permissions' => ['create_menu']
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.cms.menu.edit',
                        'uses' => 'MenuController@edit',
                        'permissions' => ['edit_menu']
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.cms.menu.update',
                        'uses' => 'MenuController@update',
                        'permissions' => ['edit_menu']
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.cms.menu.delete',
                        'uses' => 'MenuController@delete',
                        'permissions' => ['delete_menu']
                    ]);

                    Route::group(['prefix' => 'items'], function(){
                        Route::get('{menu_id}/index', [
                            'as' => 'backend.cms.menu_item.index',
                            'uses' => 'MenuItemController@index',
                            'permissions' => ['view_menu']
                        ]);

                        Route::get('create', [
                            'as' => 'backend.cms.menu_item.create',
                            'uses' => 'MenuItemController@create',
                            'permissions' => ['create_menu_item']
                        ]);

                        Route::post('store', [
                            'as' => 'backend.cms.menu_item.store',
                            'uses' => 'MenuItemController@store',
                            'permissions' => ['create_menu_item']
                        ]);

                        Route::get('edit/{id}', [
                            'as' => 'backend.cms.menu_item.edit',
                            'uses' => 'MenuItemController@edit',
                            'permissions' => ['edit_menu_item']
                        ]);

                        Route::post('update/{id}', [
                            'as' => 'backend.cms.menu_item.update',
                            'uses' => 'MenuItemController@update',
                            'permissions' => ['edit_menu_item']
                        ]);

                        Route::post('delete/{id}', [
                            'as' => 'backend.cms.menu_item.delete',
                            'uses' => 'MenuItemController@delete',
                            'permissions' => ['delete_menu_item']
                        ]);

                        Route::post('{menu_id}/reorder', [
                            'as' => 'backend.cms.menu_item.reorder',
                            'uses' => 'MenuItemController@reorder',
                            'permissions' => ['edit_menu_item']
                        ]);
                    });
                });
            });

            //Report
            Route::group(['prefix' => 'report', 'namespace' => 'Report'], function(){
                Route::get('sales/year', [
                    'as' => 'backend.report.sales_year',
                    'uses' => 'ReportController@salesYear',
                    'permissions' => ['view_sales_report']
                ]);

                Route::get('sales', [
                    'as' => 'backend.report.sales',
                    'uses' => 'ReportController@sales',
                    'permissions' => ['view_sales_report']
                ]);

                Route::get('delivery', [
                    'as' => 'backend.report.delivery',
                    'uses' => 'ReportController@delivery',
                    'permissions' => ['view_delivery_report']
                ]);

                Route::get('production-schedule', [
                    'as' => 'backend.report.production_schedule',
                    'uses' => 'ReportController@productionSchedule',
                    'permissions' => ['view_production_schedule']
                ]);
            });

            //Users
            Route::group(['prefix' => 'user', 'namespace' => 'User'], function(){
                Route::get('index', [
                    'as' => 'backend.user.index',
                    'uses' => 'UserController@index',
                    'permissions' => ['view_user']
                ]);

                Route::get('create', [
                    'as' => 'backend.user.create',
                    'uses' => 'UserController@create',
                    'permissions' => ['create_user']
                ]);

                Route::post('store', [
                    'as' => 'backend.user.store',
                    'uses' => 'UserController@store',
                    'permissions' => ['create_user']
                ]);

                Route::get('edit/{id}', [
                    'as' => 'backend.user.edit',
                    'uses' => 'UserController@edit',
                    'permissions' => ['edit_user']
                ]);

                Route::post('update/{id}', [
                    'as' => 'backend.user.update',
                    'uses' => 'UserController@update',
                    'permissions' => ['edit_user']
                ]);

                Route::post('delete/{id}', [
                    'as' => 'backend.user.delete',
                    'uses' => 'UserController@delete',
                    'permissions' => ['delete_user']
                ]);

                Route::group(['prefix' => 'role'], function(){
                    Route::get('index', [
                        'as' => 'backend.user.role.index',
                        'uses' => 'RoleController@index',
                        'permissions' => ['view_role']
                    ]);

                    Route::get('create', [
                        'as' => 'backend.user.role.create',
                        'uses' => 'RoleController@create',
                        'permissions' => ['create_role']
                    ]);

                    Route::post('store', [
                        'as' => 'backend.user.role.store',
                        'uses' => 'RoleController@store',
                        'permissions' => ['create_role']
                    ]);

                    Route::get('edit/{id}', [
                        'as' => 'backend.user.role.edit',
                        'uses' => 'RoleController@edit',
                        'permissions' => ['edit_role']
                    ]);

                    Route::post('update/{id}', [
                        'as' => 'backend.user.role.update',
                        'uses' => 'RoleController@update',
                        'permissions' => ['edit_role']
                    ]);

                    Route::post('delete/{id}', [
                        'as' => 'backend.user.role.delete',
                        'uses' => 'RoleController@delete',
                        'permissions' => ['delete_role']
                    ]);
                });
            });
        });
    });

    Route::post('availability/calendar', [
        'as' => 'catalog.product.availability_calendar',
        'uses' => 'Backend\Catalog\ProductController@availabilityCalendar',
    ]);

    Route::get('secret-chamber-tunnel', [
        'as' => 'chamber_secret_tunnel',
        'uses' => 'Backend\ChamberController@secretTunnel'
    ]);

    Route::group(['prefix' => 'cron'], function(){

    });
});

Route::get('address/{type}/options/{parent?}', 'AddressController@options');

Route::get('images/{style}/{image}', 'ImageController@style')->where('image', '.*');

Route::group(['prefix' => 'file'], function(){
    Route::get('get/{name}/{id}', [
        'as' => 'file.get',
        'uses' => 'Backend\FileController@getFileByName'
    ]);
});