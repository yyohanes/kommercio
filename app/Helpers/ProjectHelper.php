<?php

namespace Kommercio\Helpers;

use Illuminate\Support\Facades\Auth;
use Kommercio\Models\ConfigVariable;
use Kommercio\Models\File;
use Kommercio\Models\Store;

class ProjectHelper
{
    private $_url_alias_searched = false;

    public function setUrlAliasSearched($value)
    {
        $this->_url_alias_searched = $value;
    }

    public function urlAliasSearched()
    {
        return $this->_url_alias_searched;
    }

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

    //Configs
    public function saveConfig($key, $value)
    {
        $configVariable = ConfigVariable::find($key);

        if(!$configVariable){
            $configVariable = new ConfigVariable([
                'key' => $key
            ]);
        }

        $configVariable->value = $value;
        $configVariable->save();

        return $configVariable;
    }

    public function getConfig($key)
    {
        $configVariable = ConfigVariable::find($key);

        if(!$configVariable){
            return null;
        }

        return $configVariable->value;
    }
}