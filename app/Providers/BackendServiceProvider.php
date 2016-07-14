<?php

namespace Kommercio\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\File;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;
use Illuminate\Support\Facades\Storage;
use Kommercio\Models\Interfaces\UrlAliasInterface;
use Kommercio\Models\UrlAlias;

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

        $this->app['events']->listen('eloquent.saved*', function ($model) {
            if ($model instanceof UrlAliasInterface) {
                UrlAlias::saveAlias($model->getUrlAlias(), $model);
            }
        });

        $this->app['events']->listen('eloquent.deleting*', function ($model) {
            if ($model instanceof UrlAliasInterface) {
                if(!property_exists($model, 'forceDeleting') || (property_exists($model, 'forceDeleting') && $model->forceDeleting)){
                    UrlAlias::deleteAlias($model->getInternalPathSlug().'/'.$model->id);
                }
            }

            if ($model instanceof File || is_a($model, 'Kommercio\Models\File')) {
                $storage = !empty($model->storage)?$model->storage:config('filesystems.default');
                $folder = rtrim($model->folder, '/') . '/';

                if(Storage::disk($storage)->exists($folder.$model->filename)){
                    Storage::disk($storage)->delete($folder.$model->filename);
                }
            }
        });

        view()->composer(['project::backend.*', 'backend.*'], function ($view) {
            $activeStore = ProjectHelper::getActiveStore();
            $managedStores = Auth::check()?Auth::user()->getManagedStores():[];
            $otherStores = [];

            foreach($managedStores as $managedStore){
                if($activeStore->id != $managedStore->id){
                    $otherStores[] = $managedStore;
                }
            }

            $view->with('activeStore', $activeStore);
            $view->with('managedStores', $managedStores);
            $view->with('otherStores', $otherStores);
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