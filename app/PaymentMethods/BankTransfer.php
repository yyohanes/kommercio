<?php

namespace Kommercio\PaymentMethods;

use Illuminate\Http\Request;
use Kommercio\Models\Order\Order;
use Kommercio\Models\PaymentMethod\PaymentMethod;

class BankTransfer extends PaymentMethodAbstract
{
    /**
     * @inheritdoc
     */
    public function availableLocations()
    {
        return [PaymentMethod::LOCATION_CHECKOUT, PaymentMethod::LOCATION_BACKOFFICE];
    }
}