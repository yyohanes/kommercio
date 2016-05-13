<?php

namespace Kommercio\Helpers;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

class NavigationHelper
{
    public function activeClass($url)
    {
        $routeUri = Request::route()->getUri();

        return strpos($routeUri, config('kommercio.backend_prefix').'/'.$url) !== FALSE;
    }

    public function getBackUrl()
    {
        $backUrl = Request::get('backUrl');

        if(!$backUrl){
            $backUrl = route($this->predictPreviousPage());
        }

        return $backUrl;
    }

    protected function predictPreviousPage()
    {
        $routeAction = Request::route()->getAction();

        $explodedSections = explode('.', $routeAction['as']);

        array_pop($explodedSections);
        $explodedSections[] = 'index';

        $previousSection = implode('.', $explodedSections);

        if(!Route::getRoutes()->hasNamedRoute($previousSection)){
            $previousSection = 'backend.dashboard';
        }

        return $previousSection;
    }
}