<?php

namespace Kommercio\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton('project_helper', 'Kommercio\Helpers\ProjectHelper');
        $this->app->singleton('currency_helper', 'Kommercio\Helpers\CurrencyHelper');
        $this->app->singleton('price_formatter', 'Kommercio\Helpers\PriceFormatter');
        $this->app->singleton('order_helper', 'Kommercio\Helpers\OrderHelper');
        $this->app->singleton('address_helper', 'Kommercio\Helpers\AddressHelper');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
