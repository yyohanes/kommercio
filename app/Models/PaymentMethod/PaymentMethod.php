<?php

namespace Kommercio\Models\PaymentMethod;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Order\Payment;
use Kommercio\Traits\Model\HasDataColumn;

class PaymentMethod extends Model
{
    use Translatable, HasDataColumn;

    const LOCATION_CHECKOUT = 'checkout';
    const LOCATION_INVOICE = 'invoice';
    const LOCATION_BACKOFFICE = 'backoffice';

    public $timestamps = FALSE;
    public $translatedAttributes = ['name', 'message'];
    public $location;

    protected $fillable = ['name', 'class', 'message', 'sort_order', 'active'];
    protected $casts = [
        'active' => 'boolean'
    ];

    private $_processor;

    //Relations
    public function payments()
    {
        return $this->hasMany('Kommercio\Models\Order\Payment');
    }

    public function orders()
    {
        return $this->hasMany('Kommercio\Models\Order\Order');
    }

    public function stores()
    {
        return $this->morphToMany('Kommercio\Models\Store', 'store_attachable');
    }

    //Methods
    public function getProcessor()
    {
        if(!isset($this->_processor)){
            $this->_processor = null;

            $classNames = [
                'Project\Project\PaymentMethods\\'.$this->class,
                'Kommercio\PaymentMethods\\'.$this->class,
            ];

            foreach($classNames as $className){
                if(class_exists($className)){
                    $this->_processor = new $className();
                    $this->_processor->setPaymentMethod($this);
                    break;
                }
            }
        }

        return $this->_processor;
    }

    /**
     * Render payment method form
     *
     * @param Order $order Order from which to render payment form
     * @return null|string
     * @throws \Exception
     * @throws \Throwable
     */
    public function renderForm(Order $order)
    {
        return $this->getProcessor()->getCheckoutForm($order);
    }

    public function renderSummary(Order $order)
    {
        return $this->getProcessor()->getSummary($order);
    }

    //Statics

    /**
     * Get payment method options
     *
     * @param null $options Possible keys: order | request
     * @param string $location Location where payment methods will be shown
     * @return array
     */
    public static function getPaymentMethods($options = null, $location = self::LOCATION_CHECKOUT)
    {
        $order = isset($options['order'])?$options['order']:new Order();
        $options['frontend'] = in_array($location, [self::LOCATION_CHECKOUT, self::LOCATION_INVOICE]);
        $options['location'] = $location;
        $paymentMethods = self::orderBy('sort_order', 'ASC')->get();

        $request = isset($options['request'])?$options['request']:null;

        // Determine store
        $store = $order->store;

        if(!$store && $request){
            $store = ProjectHelper::getStoreByRequest($request);
        }elseif(!$store){
            $store = ProjectHelper::getActiveStore();
        }

        // Loop through all active payment methods and validate by:
        // Is active, can be used at selected store, is frontend request, custom validation in the processor
        $return = [];
        foreach($paymentMethods as $paymentMethod){
            $paymentMethod->location = $location;

            if(($paymentMethod->stores->count() < 1 || $paymentMethod->stores->pluck('id')->contains($store->id) || !$options['frontend']) && $paymentMethod->active && $paymentMethod->getProcessor() && $paymentMethod->getProcessor()->validate($options)){
                $return[] = $paymentMethod;
            }
        }

        return $return;
    }
}
