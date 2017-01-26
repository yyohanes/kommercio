<?php

namespace Kommercio\Models\ShippingMethod;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Facades\PriceFormatter;
use Kommercio\Models\Order\Order;

class ShippingMethod extends Model
{
    use Translatable;

    public $timestamps = FALSE;

    public $translatedAttributes = ['name','message'];

    protected $fillable = ['name', 'class', 'taxable', 'message', 'sort_order'];

    private $_processor;

    //Methods
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
        $shippingMethods = self::orderBy('sort_order', 'ASC')->get();

        $return = [];
        foreach($shippingMethods as $shippingMethod){
            if($shippingMethod->validate($options)){
                $shippingReturnedMethods = $shippingMethod->getPrices($options);

                if($shippingReturnedMethods){
                    $return = array_merge($return, $shippingReturnedMethods);
                }
            }
        }

        return $return;
    }
}
