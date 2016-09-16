<?php

namespace Kommercio\PaymentMethods;

use Illuminate\Http\Request;
use Kommercio\Models\PaymentMethod\PaymentMethod;

interface PaymentMethodInterface
{
    public function setPaymentMethod(PaymentMethod $shippingMethod);
    public function validate($options);
    public function getCheckoutForm($options);
    public function getValidationRules($options);
    public function processPayment($options);
    public function finalProcessPayment($options);
    public function stepPaymentMethodValidation($options);
    public function paymentMethodValidation($options);
}