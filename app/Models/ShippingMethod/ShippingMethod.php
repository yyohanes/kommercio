<?php

namespace Kommercio\Models\ShippingMethod;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Facades\PriceFormatter;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Order\Order;

class ShippingMethod extends Model
{
    use Translatable;

    public $timestamps = FALSE;

    public $translatedAttributes = ['name','message'];

    protected $fillable = ['name', 'class', 'taxable', 'message', 'sort_order', 'active'];
    protected $casts = [
        'active' => 'boolean'
    ];

    private $_processor;

    // Relations
    public function stores()
    {
        return $this->morphToMany('Kommercio\Models\Store', 'store_attachable');
    }

    // Methods
    public function getProcessor()
    {
        if(!isset($this->_processor)){
            $this->_processor = null;

            $classNames = [
                'Project\Project\ShippingMethods\\'.$this->class,
                'Kommercio\ShippingMethods\\'.$this->class,
            ];

            foreach($classNames as $className){
                if(class_exists($className)){
                    $this->_processor = new $className();
                    $this->_processor->setShippingMethod($this);
                    break;
                }
            }
        }

        return $this->_processor;
    }

    public function getSelectedMethod($key)
    {
        $methods = $this->getProcessor()->getAvailableMethods();

        return isset($methods[$key])?$methods[$key]:null;
    }

    public function validate($options = [])
    {
        if(!$this->getProcessor()){
            return false;
        }

        return $this->getProcessor() && $this->getProcessor()->validate($options);
    }

    public function getPrices($options = [])
    {
        if(!$this->getProcessor()){
            return false;
        }

        $prices = $this->getProcessor()->getPrices($options);
        foreach($prices as &$price){
            $price['price']['amount'] = PriceFormatter::round(CurrencyHelper::convert($price['price']['amount'], $price['price']['currency']));
            $price['price']['currency'] = CurrencyHelper::getCurrentCurrency()['code'];
        }

        return $prices;
    }

    //Accessors
    public function getRequireAddressAttribute()
    {
        return $this->getProcessor()->requireAddress();
    }

    //Statics
    public static function getAvailableMethods()
    {
        $shippingMethods = self::orderBy('sort_order', 'ASC')->get();

        $return = [];
        foreach($shippingMethods as $shippingMethod){
            $shippingReturnedMethods = $shippingMethod->getProcessor()->getAvailableMethods();

            if($shippingReturnedMethods){
                $return = array_merge($return, $shippingReturnedMethods);
            }
        }

        return $return;
    }

    public static function getShippingMethodObjects()
    {
        $qb = self::orderBy('sort_order', 'ASC');

        return $qb->get();
    }

    public static function getShippingMethods($options = null)
    {
        $order = isset($options['order'])?$options['order']:new Order();
        $shippingMethods = self::orderBy('sort_order', 'ASC')->get();

        $request = isset($options['request'])?$options['request']:null;

        $store = $order->store;

        if(!$store && $request){
            $store = ProjectHelper::getStoreByRequest($request);
        }elseif(!$store){
            $store = ProjectHelper::getActiveStore();
        }

        $return = [];
        foreach($shippingMethods as $shippingMethod){
            if(($shippingMethod->stores->count() < 1 || $shippingMethod->stores->pluck('id')->contains($store->id)) && $shippingMethod->active && $shippingMethod->validate($options)){
                $shippingReturnedMethods = $shippingMethod->getPrices($options);

                if($shippingReturnedMethods){
                    $return = array_merge($return, $shippingReturnedMethods);
                }
            }
        }

        return $return;
    }
}
