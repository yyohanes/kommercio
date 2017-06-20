<?php

namespace Kommercio\ShippingMethods;

use Kommercio\Models\ShippingMethod\ShippingMethod;

abstract class ShippingMethodAbstract
{
    protected $shippingMethod;

    public function getAvailableMethods()
    {
        return [];
    }

    public function setShippingMethod(ShippingMethod $shippingMethod)
    {
        $this->shippingMethod = $shippingMethod;
    }

    public function validate($options = null)
    {
        return true;
    }

    public function getPrices($options = null)
    {
        return [];
    }

    public function requireAddress()
    {
        return TRUE;
    }
}
