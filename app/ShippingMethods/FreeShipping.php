<?php

namespace Kommercio\ShippingMethods;

use Illuminate\Support\Facades\Auth;
use Kommercio\Models\Order\Order;
use Kommercio\Models\ShippingMethod\ShippingMethod;
use Kommercio\ShippingMethods\ShippingMethodInterface;

class FreeShipping extends ShippingMethodAbstract
{
    public function getAvailableMethods()
    {
        $methods = [
            'free_shipping' => [
                'shipping_method_id' => $this->shippingMethod->id,
                'name' => 'Free Shipping',
                'description' => '',
                'taxable' => $this->shippingMethod->taxable
            ]
        ];

        return $methods;
    }

    public function validate($options = null)
    {
        $user = Auth::user();

        $valid = FALSE;

        $order = $options['order']?:null;
        if($order){
            $valid = $order->eligibleForFreeShipping();
        }

        if($user && !$user->isCustomer){
            $valid = TRUE;
        }

        return $valid;
    }

    public function getPrices($options = null)
    {
        $methods = $this->getAvailableMethods();

        foreach($methods as &$method){
            $method['price'] = [
                'currency' => 'idr',
                'amount' => 0
            ];
        }

        return $methods;
    }
}