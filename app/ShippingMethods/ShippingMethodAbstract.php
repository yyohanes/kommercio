<?php

namespace Kommercio\ShippingMethods;

use Carbon\Carbon;
use Kommercio\Models\Order\DeliveryOrder\DeliveryOrder;
use Kommercio\Models\ShippingMethod\ShippingMethod;
use Kommercio\Models\Order\Order;

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

    public function useCustomPackagingSlip(DeliveryOrder $deliveryOrder)
    {
        return false;
    }

    public function customPackagingSlip(DeliveryOrder $deliveryOrder)
    {
        return false;
    }

    /**
     * Method called on new order
     * @param Order $order
     */
    public function handleNewOrder(Order $order)
    {
        // Stub
    }

    /**
     * This method is intended to be used to get availability per day by time slots
     * This is not ready for use.
     * @param Carbon $datetime
     * @param array $options
     * @return array
     */
    public function getDayAvailability(Carbon $datetime, array $options = [])
    {
        return [];
    }
}
