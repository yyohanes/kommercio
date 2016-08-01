<?php

namespace Kommercio\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Kommercio\Facades\FrontendHelper;

class FrontendServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer(['project::frontend.*', 'frontend.*'], function ($view) {
            $user = Auth::user();
            $view->with('loggedInUser', $user);

            $viewsData = $view->getData();

            $view->with('currentOrder', FrontendHelper::getCurrentOrder());

            $isHomepage = FrontendHelper::isHomepage();
            $view->with('isHomepage', $isHomepage);

            $meta_title = config('project.client_name');
            $meta_description = config('project.client_subtitle');

            if(isset($viewsData['seoModel'])){
                $meta_title = FrontendHelper::generatePageTitle($viewsData['seoModel']->getMetaTitle());

                $meta_description = $viewsData['seoModel']->getMetaDescription();
            }

            $view->with('meta_title', $meta_title);
            $view->with('meta_description', $meta_description);
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