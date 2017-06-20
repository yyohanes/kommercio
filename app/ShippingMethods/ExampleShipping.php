<?php

namespace Kommercio\ShippingMethods;

use Kommercio\Facades\CurrencyHelper;

/**
 * Sample Shipping Method processor
 */
class ExampleShipping extends ShippingMethodAbstract
{
    /**
     * @inheritDoc
     */
    public function getAvailableMethods()
    {
        $methods = [
            'example_shipping' => [
                'shipping_method_id' => $this->shippingMethod->id,
                'name' => 'Example Shipping',
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
