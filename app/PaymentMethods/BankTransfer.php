<?php

namespace Kommercio\PaymentMethods;

use Illuminate\Http\Request;
use Kommercio\Models\PaymentMethod\PaymentMethod;

class BankTransfer implements PaymentMethodInterface
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
        return true;
    }

    public function stepPaymentMethodValidation($options = null)
    {
        return true;
    }

    public function paymentMethodValidation($options = null)
    {
        return true;
    }
}