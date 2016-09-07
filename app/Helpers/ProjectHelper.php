<?php

namespace Kommercio\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Session;
use Kommercio\Events\StoreEvent;
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

    public function getStoreByRequest(Request $request, $default = null)
    {
        $store = $default;

        if(!$store){
            $store = $this->getActiveStore();
        }

        $returnedStore = Event::fire(new StoreEvent('determine_store_by_request', $request));

        if(!isset($returnedStore[0]) || empty($returnedStore[0])){
            if($request->has('store_id')){
                $store = Store::findOrFail($request->input('store_id'));
            }
        }else{
            $store = $returnedStore[0];
        }

        return $store;
    }

    public function getDefaultStore()
    {
        $defaultStore = Store::where('default', 1)->first();

        return $defaultStore;
    }

    public function getActiveStore()
    {
        if(!Auth::check()){
            $activeStore = Store::where('default', 1)->first();
        }else{
            $user = Auth::user();

            if($user->isCustomer){
                $activeStore = Store::where('default', 1)->first();
            }else{
                /*
                if(config('project.enable_store_selector', FALSE)){
                    $activeStoreId = Session::get('active_store', function() use ($user){
                        if($user->isSuperAdmin){
                            $activeStore = $this->getDefaultStore();
                        }else{
                            $activeStore = $user->stores->first();
                        }

                        Session::put('active_store', $activeStore->id);

                        return $activeStore->id;
                    });

                    $activeStore = Store::find($activeStoreId);
                }else{
                    $activeStore = $this->getDefaultStore();
                }
                */

                $activeStoreId = Session::get('active_store', function() use ($user){
                    if($user->isSuperAdmin){
                        $activeStore = $this->getDefaultStore();
                    }else{
                        $activeStore = $user->stores->first();
                    }

                    Session::put('active_store', $activeStore->id);

                    return $activeStore->id;
                });

                $activeStore = Store::find($activeStoreId);
            }
        }

        return $activeStore?:$this->getDefaultStore();
    }

    public function getDaysOptions()
    {
        $days = [
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Saturday',
            'sunday' => 'Sunday',
        ];

        return $days;
    }

    public function findViewTemplate($templates = [])
    {
        foreach($templates as $template){
            $viewPath = 'project::'.$template;

            if(view()->exists($viewPath)){
                return $viewPath;
            }
        }

        foreach($templates as $template){
            $viewPath = $template;

            if(view()->exists($viewPath)){
                return $viewPath;
            }
        }
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
    public function saveSiteConfig($key, $value)
    {
        $configVariable = ConfigVariable::find($key);

        if(!$configVariable){
            $configVariable = new ConfigVariable([
                'key' => $key
            ]);
        }

        $configVariable->value = $value;

        if(is_null($value)){
            $configVariable->delete();
        }else{
            $configVariable->save();
        }

        return $configVariable;
    }

    public function getSiteConfig($key, $default = null)
    {
        $configVariable = ConfigVariable::find($key);

        if(!$configVariable){
            return $default;
        }

        return $configVariable->value;
    }

    public function getConfig($key, $default = null)
    {
        return config('project.'.$key, config('kommercio.'.$key, $default));
    }

    public function getClientName()
    {
        return config('project.client_name');
    }

    public function getClientSubtitle()
    {
        return config('project.client_subtitle');
    }
}