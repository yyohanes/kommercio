<?php

namespace Kommercio\PaymentMethods;

use Kommercio\Models\Order\Order;
use Kommercio\Models\PaymentMethod\PaymentMethod;

abstract class PaymentMethodAbstract
{
    public $paymentMethod;

    /**
     * Validate if Payment method can be selected
     */
    public function validate($options = null)
    {
        $valid = TRUE;

        return $valid;
    }

    /**
     * Determine if Checkout externally
     */
    public function isExternalCheckout()
    {
        return false;
    }

    /**
     * Assign Payment Method object
     */
    public function setPaymentMethod(PaymentMethod $paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * Return view to add Payment method form
     */
    public function getCheckoutForm(Order $order, $options = null)
    {
        return false;
    }

    /**
     * Return view to render message after payment method is selected
     */
    public function getSummary(Order $order, $options = null)
    {
        return false;
    }

    /**
     * Additional rules to add when selecting Payment method
     */
    public function getValidationRules($options = null)
    {
        return [];
    }

    /**
     * Process after Payment is selected
     */
    public function processPayment($options = null)
    {
        return true;
    }

    /**
     * Final process (last chance to validate) before Order is placed
     * Possible Scenario: Credit Card, charge here;
     * @return mixed Return anything but 'array' as success.
     *               Return an array with 'errors' key for error messages;
     */
    public function finalProcessPayment($options = null)
    {
        return true;
    }

    /**
     * Validate upon selecting Payment method
     */
    public function stepPaymentMethodValidation($options = null)
    {
        return true;
    }

    /**
     * Validate upon placing Order
     */
    public function paymentMethodValidation($options = null)
    {
        return true;
    }
}