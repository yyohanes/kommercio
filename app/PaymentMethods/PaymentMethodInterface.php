<?php

namespace Kommercio\PaymentMethods;

use Illuminate\Http\Request;
use Kommercio\Models\PaymentMethod\PaymentMethod;

interface PaymentMethodInterface
{
    public function setPaymentMethod(PaymentMethod $shippingMethod);
    public function validate($options = null);
    public function getCheckoutForm($options = null);
    public function processPayment($options = null);
    public static function additionalValidation(Request $request);
}