<?php

namespace Kommercio\ShippingMethods;

use Kommercio\Models\Order\Order;
use Kommercio\Models\ShippingMethod\ShippingMethod;

class StandardDelivery implements ShippingMethodInterface
{
    protected $shippingMethod;

    public function validate($options = null)
    {
        $valid = TRUE;

        return $valid;
    }

    public function setShippingMethod(ShippingMethod $shippingMethod)
    {
        $this->shippingMethod = $shippingMethod;
    }

    public function getMethods($options = null)
    {
        $methods = [
            'standard_delivery' => [
                'shipping_method_id' => $this->shippingMethod->id,
                'name' => 'Standard Delivery',
                'description' => 'This is a standard delivery.',
                'price' => [
                    'currency' => 'idr',
                    'amount' => 10000
                ]
            ],
            'express_delivery' => [
                'shipping_method_id' => $this->shippingMethod->id,
                'name' => 'Express Delivery',
                'description' => 'This is a express delivery.',
                'price' => [
                    'currency' => 'idr',
                    'amount' => 20000
                ]
            ]
        ];

        return $methods;
    }

    public function beforePlaceOrder(Order $order)
    {

    }
}