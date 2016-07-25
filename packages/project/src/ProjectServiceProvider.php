<?php

namespace Project;

use Illuminate\Support\ServiceProvider;

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

        if(file_exists(base_path('packages/project/src/Project/ServiceProviderBridge.php'))){
            \Project\Project\ServiceProviderBridge::onBoot($this->app);
        }
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

        if(file_exists(base_path('packages/project/src/Project/ServiceProviderBridge.php'))) {
            \Project\Project\ServiceProviderBridge::onRegister($this->app);
        }

        include_once __DIR__.'/Project/routes.php';
    }
}
