<?php

namespace Kommercio\Helpers;

use Illuminate\Support\Facades\Request as RequestFacade;
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

    public function formatNumber($number)
    {
        $activeCurrency = \CurrencyHelper::getCurrentCurrency();

        $currency = $activeCurrency;

        return str_replace($currency['decimal_separator'].'00', '',number_format($number, 2, $currency['decimal_separator'], $currency['thousand_separator']));
    }

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

        $returnedStore = Event::fire(new StoreEvent('determine_store_by_request', $store, $request));

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
        $defaultStore = \Kommercio\Facades\RuntimeCache::getOrSet('default_store', function(){
            return Store::where('default', 1)->first();
        });

        return $defaultStore;
    }

    public function getActiveStore()
    {
        $activeStoreId = Session::get('active_store', function(){
            if(Auth::check()){
                $user = Auth::user();

                if($user->isSuperAdmin || $user->isCustomer){
                    $activeStore = $this->getDefaultStore();
                }else{
                    $activeStore = $user->stores->first();
                }

                Session::put('active_store', $activeStore->id);

                return $activeStore->id;
            }else{
                return $this->getDefaultStore()->id;
            }
        });

        $activeStore = \Kommercio\Facades\RuntimeCache::getOrSet('store_'.$activeStoreId, function() use ($activeStoreId){
            return Store::find($activeStoreId);
        });

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

    public function isTestEnvironment()
    {
        return (env('APP_ENV') != 'production') || in_array(RequestFacade::ip(), $this->getConfig('test_ips'));
    }

    public function flattenArrayToKey($array)
    {
        $keys = [];

        ksort($array);

        foreach($array as $idx => $arrayValue){
            if($arrayValue !== null){
                $keys[] = $idx.':'.$arrayValue;
            }
        }

        return implode(';', $keys);
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

    public function isFeatureEnabled($key, $default = false)
    {
        return config('project.features.'.$key, config('features.'.$key, $default));
    }

    public function getClientName()
    {
        return config('project.client_name');
    }

    public function getClientSubtitle()
    {
        return config('project.client_subtitle');
    }

    public function generateUuid() {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0x0fff ) | 0x4000,
            mt_rand( 0, 0x3fff ) | 0x8000,
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }

    public function objectToArray($obj) {
        if(is_object($obj)) $obj = (array) $obj;
        if(is_array($obj)) {
            $new = array();
            foreach($obj as $key => $val) {
                $new[$key] = $this->objectToArray($val);
            }
        }
        else $new = $obj;
        return $new;
    }
}