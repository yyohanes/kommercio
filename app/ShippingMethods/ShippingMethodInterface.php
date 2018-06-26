<?php

namespace Kommercio\ShippingMethods;

use Kommercio\Models\Order\DeliveryOrder\DeliveryOrder;
use Kommercio\Models\ShippingMethod\ShippingMethod;

interface ShippingMethodInterface
{
    public function getAvailableMethods();
    public function setShippingMethod(ShippingMethod $shippingMethod);
    public function validate($options = null);
    public function getPrices($options = null);
    public function requireAddress();
    public function useCustomPackagingSlip(DeliveryOrder $deliveryOrder);
    public function customPackagingSlip(DeliveryOrder $deliveryOrder);
}
