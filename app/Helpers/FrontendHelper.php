<?php

namespace Kommercio\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Request as RequestFacade;
use Kommercio\Facades\ProjectHelper as ProjectHelperFacade;
use Illuminate\Http\Request;
use Kommercio\Models\CMS\BannerGroup;
use Kommercio\Models\CMS\Block;
use Kommercio\Models\CMS\Menu;
use Kommercio\Models\CMS\MenuItem;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Product;
use Kommercio\Models\UrlAlias;

class FrontendHelper
{
    private $_currentOrder;

    private $_miniAliasCache;

    public function getAlias($internal_path)
    {
        $locale = App::getLocale();

        if(isset($this->_miniAliasCache[$internal_path.':'.$locale])){
            $path = $this->_miniAliasCache[$internal_path.':'.$locale];
        }else{
            $urlAlias = UrlAlias::where('internal_path', $internal_path)
                ->where('locale', $locale)
                ->first();

            if($urlAlias){
                $path = $urlAlias->external_path;
            }else{
                $path = $internal_path;
            }

            $this->_miniAliasCache[$internal_path.':'.$locale] = $path;
        }

        return $path;
    }

    public function get_url($internal_path, $params = [], $secure = null)
    {
        $path = $this->getAlias($internal_path);

        return url($path, $params, $secure);
    }

    public function getCurrentUrlWithQuery($query = [])
    {
        $path = RequestFacade::path();

        $query = array_merge(RequestFacade::query(), $query);

        return $this->get_url($path).(!empty($query)?'?'.http_build_query($query):'');
    }

    public function get_home_url()
    {
        //$homePath = config('project.home_uri');

        return url('/');
    }

    public function isHomepage()
    {
        $requestUri = RequestFacade::getRequestUri();
        $requestUri = urldecode(substr($requestUri,1));

        $homePath = config('project.home_uri');

        return $requestUri == $homePath;
    }

    public function pathIsHere($path)
    {
        $currentPath = substr(RequestFacade::getPathInfo(), 1);

        return $currentPath == $path;
    }

    public function pathIsDescendant($path)
    {
        if(is_array($path)){
            $paths = $path;
        }else{
            $paths = [$path];
        }

        $currentPath = substr(RequestFacade::getPathInfo().'/', 1);

        foreach($paths as $path){
            if(strpos($currentPath, $path) === 0){
                return true;
            }
        }

        return false;
    }

    //Menus
    public function getRootMenuItems($menu_slug)
    {
        $menuItems = [];

        if(is_array($menu_slug)){
            $menu_slugs = $menu_slug;
        }else{
            $menu_slugs = [$menu_slug];
        }

        foreach($menu_slugs as $menu_slug){
            $menu = Menu::with('rootMenuItems')->where('slug', $menu_slug)->first();

            $menuItems += ($menu->rootMenuItems->count() > 0)?$menu->rootMenuItems->all():[];
        }

        return $menuItems;
    }

    public function getMenuItemSiblings($path, $menu_slug)
    {
        $menu = Menu::where('slug', $menu_slug)->firstOrFail();
        $menuItems = $menu->menuItems()->WhereTranslation('url', $path)->pluck('parent_id')->all();

        $siblings = $menu->menuItems()->whereIn('parent_id', $menuItems)->get();

        return $siblings;
    }

    //Banners
    public function getBanners($banner_group_slug)
    {
        $bannerGroup = BannerGroup::with('banners')->where('slug', $banner_group_slug)->first();

        $banners = $bannerGroup?$bannerGroup->banners:[];

        return $banners;
    }

    //Block
    public function getBlock($machine_name)
    {
        $block = Block::where('machine_name', $machine_name)->active()->first();

        return $block;
    }

    //Products
    public function getNewProducts($take = null, $criteria = [])
    {
        $newItems = [];

        $qb = Product::isNew()->active()->catalogVisible()->productEntity()->orderBy('created_at', 'DESC');

        if($take){
            $newItems = $qb->take($take);
        }

        $newItems = $qb->get();

        return $newItems;
    }

    //Cart
    public function getSoonestDeliveryDay($format='Y-m-d')
    {
        $now = Carbon::now();

        $soonest = ProjectHelperFacade::getConfig('soonest_delivery_days');

        $now->modify('+'.$soonest.' days');

        return $now->format($format);
    }

    public function getCurrentOrder($context=null)
    {
        $cookieKey = ProjectHelperFacade::getConfig('project_machine_name', 'kommercio').'_order_id';

        if(Cookie::has($cookieKey) && !isset($this->_currentOrder)){
            $order = Order::where('id', Cookie::get($cookieKey))
                ->where('status', Order::STATUS_CART)
                ->first();

            if($order){
                $refreshInterval = $order->created_at->modify('+12 hours');
                if($order->created_at && $refreshInterval->lt(Carbon::now())){
                    $order->reset();
                }
            }

            $this->_currentOrder = $order;
        }

        if(empty($this->_currentOrder)){
            $order = new Order();
            $order->ip_address = RequestFacade::ip();
            $order->user_agent = RequestFacade::header('User-Agent');
            $order->status = Order::STATUS_CART;

            $this->_currentOrder = $order;

            if($context == 'save'){
                $order->save();

                $cookie = Cookie::make($cookieKey, $order->id, 25200);
                Cookie::queue($cookie);
            }
        }

        return $this->_currentOrder;
    }

    public function generatePageTitle($text)
    {
        $isHomepage = FrontendHelper::isHomepage();

        if($isHomepage){
            $title = $text;
        }else{
            $title = $text.' - '.config('project.client_name');
        }

        return $title;
    }
}