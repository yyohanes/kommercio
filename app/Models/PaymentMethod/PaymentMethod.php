<?php

namespace Kommercio\Models\PaymentMethod;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Interfaces\CacheableInterface;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Order\Payment;
use Kommercio\Models\Store;
use Kommercio\Models\ShippingMethod\ShippingMethod;
use Kommercio\Traits\Model\HasDataColumn;

class PaymentMethod extends Model implements CacheableInterface
{
    use Translatable, HasDataColumn;

    const LOCATION_CHECKOUT = 'checkout';
    const LOCATION_INVOICE = 'invoice';
    const LOCATION_BACKOFFICE = 'backoffice';

    public $timestamps = FALSE;
    public $translatedAttributes = ['name', 'message'];
    public $location;

    public $fillable = ['name', 'class', 'message', 'sort_order', 'active'];
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

    /**
     * Get shipping method that are allowed to use this payment method
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function shippingMethods() {
        return $this->belongsToMany(ShippingMethod::class);
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
     * Check if payment method is available at given store
     * @param  Store   $store
     * @return bool
     */
    public function isAvailableAtStore(Store $store)
    {
        return $this->stores->count() < 1 || $this->stores->pluck('id')->contains($store->id);
    }

    /**
     * Check if payment method is available with given shipping method
     * @param  Store   $store
     * @return bool
     */
    public function isAvailableWithShippingMethod(ShippingMethod $shippingMethod)
    {
        return is_null($shippingMethod)
            || $this->shippingMethods->count() < 1
            || ($shippingMethod && $this->shippingMethods->pluck('id')->contains($shippingMethod->id));
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

    public function isExternal() {
        return $this->getProcessor()->isExternal();
    }

    public function getPublicData() {
        return $this->getProcessor()->getPublicData();
    }

    //Statics

    /**
     * Get payment method options
     *
     * @param  array $options Available option key: ['order']: Order object, ['request']: Request object, ['show_all_active']: show all active methods
     * @param string $location Location where payment methods will be shown
     * @return array
     */
    public static function getPaymentMethods($options = null, $location = self::LOCATION_CHECKOUT)
    {
        $order = isset($options['order'])?$options['order']:new Order();
        $options['frontend'] = in_array($location, [self::LOCATION_CHECKOUT, self::LOCATION_INVOICE]);
        $options['location'] = $location;
        $options['show_all_active'] = isset($options['show_all_active'])?$options['show_all_active']:false;

        $request = isset($options['request'])?$options['request']:null;

        // Determine store
        $store = $order->store;

        if(!$store && $request){
            $store = ProjectHelper::getStoreByRequest($request);
        }elseif(!$store){
            $store = ProjectHelper::getActiveStore();
        }

        // Get shipping method from Request or Order respectively

        $options['shipping_method'] = null;

        if ($request && $request->has('shipping_method')) {
            $options['shipping_method'] = ShippingMethod::find($request->input('shipping_method'));
        }

        if (!$options['shipping_method']) {
            $options['shipping_method'] = $order->getShippingMethod();
        }

        if (!$options['show_all_active'] && $options['shipping_method'] instanceof ShippingMethod && $options['shipping_method']->paymentMethods->count() > 0) {
            $paymentMethods = $options['shipping_method']->paymentMethods;
        } else {
            $paymentMethods = self::orderBy('sort_order', 'ASC')->get();
        }

        // Loop through all active payment methods and validate by:
        // Is active, can be used at selected store, is frontend request, custom validation in the processor
        $return = collect([]);
        foreach($paymentMethods as $paymentMethod){
            $paymentMethod->location = $location;

            if(($paymentMethod->isAvailableAtStore($store) || !$options['frontend'])
                && $paymentMethod->active
                && ($paymentMethod->getProcessor() && $paymentMethod->getProcessor()->validate($options))
                ){
                $return[] = $paymentMethod;
            }
        }

        return $return;
    }

    public static function findById(int $id) {
        $tableName = (new static)->getTable();

        return Cache::remember($tableName . '_' . $id, 25200, function() use ($id) {
            return static::find($id);
        });
    }

    public static function findByClass(string $class) {
        $tableName = (new static)->getTable();

        return Cache::remember($tableName . '_' . $class, 25200, function() use ($class) {
            return static::where('class', $class)->first();
        });
    }
}
