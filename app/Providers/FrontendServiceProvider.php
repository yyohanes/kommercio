<?php

namespace Kommercio\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Kommercio\Facades\FrontendHelper;
use Kommercio\Facades\ProjectHelper;

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
            $testEnvironment = (env('APP_ENV') != 'production') || in_array(Request::ip(), ProjectHelper::getConfig('test_ips'));
            $view->with('testEnvironment', $testEnvironment);

            $user = Auth::user();
            $view->with('loggedInUser', $user);
            $view->with('loggedInCustomer', $user?$user->customer:null);

            $viewsData = $view->getData();

            $view->with('currentOrder', FrontendHelper::getCurrentOrder());

            $isHomepage = FrontendHelper::isHomepage();
            $view->with('isHomepage', $isHomepage);

            $meta_title = ProjectHelper::getClientName();
            $meta_description = ProjectHelper::getClientSubtitle();
            $meta_image = null;

            if(isset($viewsData['seoModel'])){
                $meta_title = $viewsData['seoModel']->getMetaTitle();
                $meta_description = $viewsData['seoModel']->getMetaDescription();
                $meta_image = $viewsData['seoModel']->getMetaImage();
            }elseif(isset($viewsData['seoData'])){
                extract($viewsData['seoData']);
            }

            $meta_title = FrontendHelper::generatePageTitle($meta_title);

            $view->with('meta_title', $meta_title);
            $view->with('meta_description', $meta_description);
            $view->with('meta_image', $meta_image);
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