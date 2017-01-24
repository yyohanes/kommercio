<?php

namespace Kommercio\PaymentMethods;

use Illuminate\Http\Request;
use Kommercio\Models\PaymentMethod\PaymentMethod;

class CashOnDelivery implements PaymentMethodInterface
{
    protected $paymentMethod;

    public function validate($options = null)
    {
        $valid = true;
        $order = isset($options['order'])?$options['order']:null;

        if($order && $order->getShippingMethod()){
            $valid = $order->shippingInformation->country && $order->shippingInformation->country->iso_code == 'SG';
        }

        return $valid;
    }

    public function isExternalCheckout()
    {
        return false;
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