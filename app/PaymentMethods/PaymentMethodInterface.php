<?php

namespace Kommercio\PaymentMethods;

use Illuminate\Http\Request;
use Kommercio\Models\PaymentMethod\PaymentMethod;

interface PaymentMethodInterface
{
    /*
     * Assign Payment Method object
     */
    public function setPaymentMethod(PaymentMethod $shippingMethod);

    /*
     * Validate if Payment method can be selected
     */
    public function validate($options);

    /*
     * Determine if Checkout externally
     */
    public function isExternalCheckout();

    /*
     * Return view to add Payment method form
     */
    public function getCheckoutForm($options);

    /*
     * Additional rules to add when selecting Payment method
     */
    public function getValidationRules($options);

    /*
     * Validate upon selecting Payment method
     */
    public function stepPaymentMethodValidation($options);

    /*
     * Process after Payment is selected
     */
    public function processPayment($options);

    /*
     * Validate upon placing Order
     */
    public function paymentMethodValidation($options);

    /*
     * Final process (last chance to validate) before Order is placed
     */
    public function finalProcessPayment($options);
}