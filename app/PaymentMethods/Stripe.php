<?php

namespace Kommercio\PaymentMethods;

use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\PaymentMethod\PaymentMethod;
use Illuminate\Http\Request;

class Stripe implements PaymentMethodInterface
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
        return ProjectHelper::getViewTemplate('frontend.order.payment_method.stripe');
    }

    public function processPayment($options = null)
    {

    }

    //Statics
    public static function additionalValidation(Request $request)
    {
        return [
            'data.api_key' => 'required'
        ];
    }
}