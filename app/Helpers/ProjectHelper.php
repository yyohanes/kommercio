<?php

namespace Kommercio\Helpers;

use Illuminate\Support\Facades\Auth;
use Kommercio\Models\File;
use Kommercio\Models\Store;

class ProjectHelper
{
    public function getMaxUploadSize()
    {
        return intval(File::MAXIMUM_SIZE);
    }

    public function getDefaultStore()
    {
        $defaultStore = Store::where('default', 1)->first();

        return $defaultStore;
    }

    public function getActiveStore()
    {
        $user = Auth::user();

        if($user->isSuperAdmin){
            $defaultStore = Store::where('default', 1)->first();
        }else{
            $defaultStore = $user->stores->first();
        }

        return $defaultStore;
    }

    public function getViewTemplate($template)
    {
        $viewPath = 'project::'.$template;

        if(!view()->exists($viewPath)){
            $viewPath = $template;
        }

        return $viewPath;
    }
}