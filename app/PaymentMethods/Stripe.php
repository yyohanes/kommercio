<?php

namespace Kommercio\PaymentMethods;

use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\PaymentMethod\PaymentMethod;
use Illuminate\Http\Request;

class Stripe implements PaymentMethodInterface, PaymentMethodSettingFormInterface
{
    protected $paymentMethod;

    public function validate($options = null)
    {
        $valid = TRUE;

        if(isset($options['frontend'])){
            $valid = $options['frontend'];
        }

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

    public function getValidationRules($options = null)
    {
        return ['stripeToken' => 'required'];
    }

    public function processPayment($options = null)
    {
        $request = $options['request'];
        $options['order']->saveData(['stripeToken' => $request->input('stripeToken')]);
    }

    public function finalProcessPayment($options = null)
    {

    }

    public function stepPaymentMethodValidation($options = null)
    {
        return true;
    }

    public function paymentMethodValidation($options = null)
    {
        $order = $options['order'];

        \Stripe\Stripe::setApiKey($this->getSecretKey());

        try{
            $token = \Stripe\Token::retrieve($order->getData('stripeToken', null));
            \Log::info($token);
        }catch(\Exception $e){
            return false;
        }

        return false;
    }

    public function settingForm()
    {
        return ProjectHelper::getViewTemplate('backend.payment_method.stripe.additional_setting_form');
    }

    public function getPublishableKey()
    {
        return $this->paymentMethod->getData('publishable_key');
    }

    protected function getSecretKey()
    {
        return $this->paymentMethod->getData('secret_key');
    }

    //Statics
    public static function additionalSettingValidation(Request $request)
    {
        return [
            'data.secret_key' => 'required',
            'data.publishable_key' => 'required'
        ];
    }
}