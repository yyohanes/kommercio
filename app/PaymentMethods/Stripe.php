<?php

namespace Kommercio\PaymentMethods;

use Carbon\Carbon;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Order\Payment;
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
        $order = $options['order'];

        $order->saveData(['stripeToken' => $request->input('stripeToken')]);
    }

    public function finalProcessPayment($options = null)
    {
        $order = $options['order'];

        \Stripe\Stripe::setApiKey($this->getSecretKey());

        try{
            $charge = \Stripe\Charge::create(array(
                "amount" => $order->total * 100,
                "currency" => $order->currency,
                "source" => $order->getData('stripeToken'),
                "description" => "Charge for ".$order->billingInformation->email,
                "metadata" => [
                    "order_reference" => $order->reference
                ]
            ));

            $paymentData = [
                'payment_method_id' => $this->paymentMethod->id,
                'amount' => $order->total,
                'currency' => $order->currency,
                'status' => Payment::STATUS_PENDING,
                'order_id' => $order->id,
            ];

            $paymentData['notes'] = "Card Detail"."\r\n";
            $paymentData['notes'] .= "Type: ".$charge->source->brand."\r\n";
            $paymentData['notes'] .= "Country: ".$charge->source->country."\r\n";
            $paymentData['notes'] .= "Last4: ".$charge->source->last4."\r\n";

            $payment = new Payment();
            $payment->fill($paymentData);
            $payment->status = Payment::STATUS_SUCCESS;
            $payment->payment_date = Carbon::now();
            $payment->saveData(['stripe' => $charge]);
            $payment->save();
        }catch(\Stripe\Error\Base $e){
            $body = $e->getJsonBody();
            $err  = $body['error'];
            return $err['message'];
        }
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
            return !$token->used;
        }catch(\Exception $e){
            return false;
        }
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