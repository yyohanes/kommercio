<?php

namespace Kommercio\PaymentMethods;

use Kommercio\Models\PaymentMethod\PaymentMethod;

interface PaymentMethodInterface
{
    public function setPaymentMethod(PaymentMethod $shippingMethod);
    public function validate($options = null);
}