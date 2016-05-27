<?php

namespace Kommercio\ShippingMethods;

use Kommercio\Models\ShippingMethod\ShippingMethod;

interface ShippingMethodInterface
{
    public function setShippingMethod(ShippingMethod $shippingMethod);
    public function validate($options = null);
    public function getMethods($options = null);
}