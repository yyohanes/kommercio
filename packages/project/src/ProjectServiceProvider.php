<?php

namespace Project;

use Illuminate\Support\ServiceProvider;
use Project\Project\ServiceProviderBridge;

class ProjectServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/Project/views', 'project');
        $this->loadTranslationsFrom(__DIR__.'/Project/translations', 'project');

        ServiceProviderBridge::onBoot($this->app);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/Project/config/project.php', 'project'
        );

        $this->mergeConfigFrom(
            __DIR__.'/Project/config/currency.php', 'project'
        );

        ServiceProviderBridge::onRegister($this->app);

        include_once __DIR__.'/Project/routes.php';
    }
}
