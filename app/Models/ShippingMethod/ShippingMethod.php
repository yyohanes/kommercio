<?php

namespace Kommercio\Models\ShippingMethod;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Facades\PriceFormatter;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Interfaces\CacheableInterface;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Store;
use Kommercio\Models\PaymentMethod\PaymentMethod;
use Kommercio\ShippingMethods\ShippingMethodInterface;

class ShippingMethod extends Model implements CacheableInterface
{
    use Translatable;

    public $timestamps = FALSE;

    public $translatedAttributes = ['name','message'];

    public $fillable = ['name', 'class', 'taxable', 'message', 'sort_order', 'active'];
    protected $casts = [
        'active' => 'boolean'
    ];

    private $_processor;

    // Relations
    /**
     * Get stores that can use this shipping method
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function stores()
    {
        return $this->morphToMany('Kommercio\Models\Store', 'store_attachable');
    }

    /**
     * Get payment methods that are allowed to use this shipping method
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function paymentMethods() {
        return $this->belongsToMany(PaymentMethod::class);
    }

    // Methods
    /**
     * Get shipping method processor / handler
     * @return ShippingMethodInterface
     */
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

    /**
     * Check if shipping method is available at given store
     * @param  Store   $store
     * @return bool
     */
    public function isAvailableAtStore(Store $store)
    {
        return $this->stores->count() < 1 || $this->stores->pluck('id')->contains($store->id);
    }

    /**
     * Check if shipping method is available with given payment method
     * @param  Store   $store
     * @return bool
     */
    public function isAvailableWithPaymentMethod(PaymentMethod $paymentMethod)
    {
        return is_null($paymentMethod)
            || $this->paymentMethods->count() < 1
            || ($paymentMethod && $this->paymentMethods->pluck('id')->contains($paymentMethod->id));
    }

    /**
     * @inheritdoc
     */
    public function getCacheKeys()
    {
        $tableName = $this->getTable();
        $keys = [
            $tableName . '_' . $this->id,
            $tableName . '_' . $this->class,
        ];

        return $keys;
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

    /**
     * Get shipping method options
     * @param  array $options Available option key: ['order']: Order object, ['request']: Request object, ['frontend']: is requested from frontend, ['show_all_active']: show all active methods
     * @return array
     */
    public static function getShippingMethods($options = null)
    {
        $order = isset($options['order'])?$options['order']:new Order();

        $request = isset($options['request'])?$options['request']:null;

        $store = $order->store;

        if(!$store && $request){
            $store = ProjectHelper::getStoreByRequest($request);
        }elseif(!$store){
            $store = ProjectHelper::getActiveStore();
        }

        $options['frontend'] = !isset($options['frontend'])?TRUE:$options['frontend'];
        $options['show_all_active'] = isset($options['show_all_active'])?$options['show_all_active']:false;

        // Get payment method from Request or Order respectively

        $options['payment_method'] = null;

        if ($request && $request->has('payment_method')) {
            $options['payment_method'] = PaymentMethod::find($request->input('payment_method'));
        }

        if (!$options['payment_method'] && $order->paymentMethod) {
            $options['payment_method'] = $order->paymentMethod;
        }

        if (!$options['show_all_active'] && $options['payment_method'] instanceof PaymentMethod && $options['payment_method']->shippingMethods->count() > 0) {
            $shippingMethods = $options['payment_method']->shippingMethods;
        } else {
            $shippingMethods = self::orderBy('sort_order', 'ASC')->get();
        }

        $return = [];
        foreach($shippingMethods as $shippingMethod){
            if($shippingMethod->isAvailableAtStore($store)
                && $shippingMethod->active
                && $shippingMethod->validate($options)
                ){
                $shippingReturnedMethods = $shippingMethod->getPrices($options);

                if($shippingReturnedMethods){
                    $return = array_merge($return, $shippingReturnedMethods);
                }
            }
        }

        return $return;
    }

    public static function findById(int $id) {
        $tableName = (new static)->getTable();

        return Cache::remember($tableName . '_' . $id, 25200, function() use ($id) {
            return static::where('id', $id)->first();
        });
    }

    public static function findByClass($class) {
        $tableName = (new static)->getTable();

        return Cache::remember($tableName . '_' . $class, 25200, function() use ($class) {
            return static::where('class', $class)->first();
        });
    }
}
