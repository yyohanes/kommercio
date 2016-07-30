<?php

namespace Kommercio\ShippingMethods;

use Kommercio\Models\Order\Order;
use Kommercio\Models\ShippingMethod\ShippingMethod;

interface ShippingMethodInterface
{
    public function getAvailableMethods();
    public function setShippingMethod(ShippingMethod $shippingMethod);
    public function validate($options = null);
    public function getPrices($options = null);
    public function requireAddress();
}