<?php

namespace Kommercio\PaymentMethods;

use Kommercio\Models\PaymentMethod\PaymentMethod;

class CashOnDelivery implements PaymentMethodInterface
{
    protected $paymentMethod;

    public function validate($options = null)
    {
        $valid = TRUE;

        return $valid;
    }

    public function setPaymentMethod(PaymentMethod $paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
    }
}