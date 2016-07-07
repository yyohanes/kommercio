<?php

namespace Kommercio\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Kommercio\Validator\CustomValidator;

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
        $this->app->singleton('email_helper', 'Kommercio\Helpers\EmailHelper');
        $this->app->singleton('language_helper', 'Kommercio\Helpers\LanguageHelper');
        $this->app->singleton('frontend_helper', 'Kommercio\Helpers\FrontendHelper');

        $this->app['validator']->resolver(function($translator, $data, $rules, $messages)
        {
            return new CustomValidator($translator, $data, $rules, $messages);
        });
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
