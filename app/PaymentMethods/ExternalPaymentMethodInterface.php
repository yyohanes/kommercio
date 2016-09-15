<?php

namespace Kommercio\PaymentMethods;

use Illuminate\Http\Request;
use Kommercio\Models\PaymentMethod\PaymentMethod;

interface ExternalPaymentMethodInterface
{
    public function getExternalPaymentForm($options = null);
}