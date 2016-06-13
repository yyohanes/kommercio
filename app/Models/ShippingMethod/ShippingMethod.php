<?php

namespace Kommercio\Models\ShippingMethod;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
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
        $methods = $this->getProcessor()->getMethods();

        return isset($methods[$key])?$methods[$key]:null;
    }

    //Statics
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
            if($shippingMethod->getProcessor() && $shippingMethod->getProcessor()->validate($options)){
                $shippingReturnedMethods = $shippingMethod->getProcessor()->getMethods($options);

                if($shippingReturnedMethods){
                    $return = array_merge($return, $shippingReturnedMethods);
                }
            }
        }

        return $return;
    }
}
