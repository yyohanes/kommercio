<?php

namespace Kommercio\ShippingMethods;

use Kommercio\Facades\CurrencyHelper;

/**
 * Shipping Method processor for handling Pick-Up method
 */
class PickUp extends ShippingMethodAbstract
{
    /**
     * @inheritDoc
     */
    public function getAvailableMethods()
    {
        $methods = [
            'pick_up' => [
                'shipping_method_id' => $this->shippingMethod->id,
                'name' => 'Pick-Up',
                'description' => '',
                'taxable' => $this->shippingMethod->taxable
            ]
        ];

        return $methods;
    }

    /**
     * @inheritDoc
     */
    public function getPrices($options = null)
    {
        $methods = $this->getAvailableMethods();

        foreach($methods as &$method){
            $method['price'] = [
                'currency' => CurrencyHelper::getCurrentCurrency()['code'],
                'amount' => 0
            ];
        }

        return $methods;
    }
}
