<?php

namespace Kommercio\PaymentMethods;

use Illuminate\Http\Request;
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

    public function getCheckoutForm($options = null)
    {
        return false;
    }

    public function getValidationRules($options = null)
    {
        return [];
    }

    public function processPayment($options = null)
    {

    }

    public function finalProcessPayment($options = null)
    {

    }

    public function paymentMethodValidation($options = null)
    {
        return true;
    }
}