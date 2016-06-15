<?php

namespace Kommercio\Providers;

use Illuminate\Support\ServiceProvider;
use Kommercio\Models\File;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;
use Illuminate\Support\Facades\Storage;

class BackendServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton('navigation_helper', 'Kommercio\Helpers\NavigationHelper');

        $this->app['events']->listen('eloquent.creating*', function ($model) {
            if ($model instanceof AuthorSignatureInterface) {
                $model->authorSign('creating');
            }
        });

        $this->app['events']->listen('eloquent.updating*', function ($model) {
            if ($model instanceof AuthorSignatureInterface) {
                $model->authorSign('updating');
            }
        });

        $this->app['events']->listen('eloquent.deleting*', function ($model) {
            if ($model instanceof File || is_a($model, 'Kommercio\Models\File')) {
                $storage = !empty($model->storage)?$model->storage:config('filesystems.default');
                $folder = rtrim($model->folder, '/') . '/';

                if(Storage::disk($storage)->exists($folder.$model->filename)){
                    Storage::disk($storage)->delete($folder.$model->filename);
                }
            }
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