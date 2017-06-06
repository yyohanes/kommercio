<?php

namespace Kommercio\PaymentMethods;

use Illuminate\Http\Request;
use Kommercio\Models\PaymentMethod\PaymentMethod;

class CashOnDelivery extends PaymentMethodAbstract
{
    /**
     * @inheritdoc
     */
    public function availableLocations()
    {
        return [PaymentMethod::LOCATION_CHECKOUT, PaymentMethod::LOCATION_BACKOFFICE];
    }
}