<?php

namespace Kommercio\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Http\Request;
use Kommercio\Models\CMS\BannerGroup;
use Kommercio\Models\CMS\Block;
use Kommercio\Models\CMS\Menu;
use Kommercio\Models\Order\Order;
use Kommercio\Models\UrlAlias;

class FrontendHelper
{
    private $_currentOrder;

    public function get_url($internal_path, $params = [], $secure = null)
    {
        $locale = App::getLocale();

        $urlAlias = UrlAlias::where('internal_path', $internal_path)
            ->where('locale', $locale)
            ->first();

        if($urlAlias){
            $path = $urlAlias->external_path;
        }else{
            $path = $internal_path;
        }

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

    //Menus
    public function getRootMenuItems($menu_slugs)
    {
        $menuItems = [];

        foreach($menu_slugs as $menu_slug){
            $menu = Menu::with('rootMenuItems')->where('slug', $menu_slug)->first();

            $menuItems += ($menu->rootMenuItems->count() > 0)?$menu->rootMenuItems->all():[];
        }

        return $menuItems;
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
        $block = Block::where('machine_name', $machine_name)->first();

        return $block;
    }

    //Cart
    public function getSoonestDeliveryDay($format='Y-m-d')
    {
        $now = Carbon::now();

        $soonest = config('project.soonest_delivery_days');

        $now->modify('+'.$soonest.' days');

        return $now->format($format);
    }

    public function getCurrentOrder($context=null)
    {
        $cookieKey = config('project.project_machine_name', 'kommercio').'_order_id';

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
}