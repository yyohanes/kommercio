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

        include_once __DIR__.'/Project/routes.php';
    }
}
