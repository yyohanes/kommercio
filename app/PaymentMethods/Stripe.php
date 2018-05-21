<?php

namespace Kommercio\PaymentMethods;

use Carbon\Carbon;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Order\Payment;
use Kommercio\Models\PaymentMethod\PaymentMethod;
use Illuminate\Http\Request;

class Stripe extends PaymentMethodAbstract implements PaymentMethodSettingFormInterface
{
    public function getCheckoutForm(Order $order, $options = null)
    {
        $view = ProjectHelper::getViewTemplate('frontend.order.payment_method.stripe');

        return view($view, ['order' => $order, 'paymentMethod' => $this->paymentMethod])->render();
    }

    public function getValidationRules($options = null)
    {
        return ['stripeToken' => 'required'];
    }

    public function processPayment($options = null)
    {
        $request = $options['request'];
        $order = $options['order'];

        if($order && $order->exists){
            $order->saveData(['stripeToken' => $request->input('stripeToken')]);
        }
    }

    public function finalProcessPayment($options = null)
    {
        $order = $options['order'];
        $request = $options['request'] ?? null;

        if($order && $order->exists){
            \Stripe\Stripe::setApiKey($this->getSecretKey());

            $currency = CurrencyHelper::getCurrency($order->currency);
            $smallestUnit = isset($currency['smallest_unit']) ? $currency['smallest_unit'] : 1;
            $stripeToken = ($request && $request->filled('stripeToken'))
                ? $request->input('stripeToken')
                : $order->getData('stripeToken');

            try{
                $charge = \Stripe\Charge::create(array(
                    "amount" => $order->getOutstandingAmount() * $smallestUnit,
                    "currency" => $order->currency,
                    "source" => $stripeToken,
                    "description" => "Charge for ".$order->shippingInformation->email,
                    "metadata" => [
                        "order_id" => $order->id,
                        "order_reference" => $order->reference,
                    ]
                ));

                $notes = "Card Detail"."\r\n";
                $notes .= "Type: ".$charge->source->brand."\r\n";
                $notes .= "Country: ".$charge->source->country."\r\n";
                $notes .= "Last4: ".$charge->source->last4."\r\n";

                $options = [
                    'data' => [
                        'stripe' => $charge
                    ]
                ];

                $invoice = isset($options['invoice'])?$options['invoice']:null;

                $payment = Payment::createPayment($order, $invoice, Payment::STATUS_SUCCESS, $this->paymentMethod, $notes, $options);

                return $payment;
            }catch(\Stripe\Error\Base $e){
                $body = $e->getJsonBody();
                $err  = $body['error'];
                return [$err['message']];
            }
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

    public function saveForm(Request $request)
    {

    }

    public function settingForm()
    {
        return ProjectHelper::getViewTemplate('backend.payment_method.Stripe.additional_setting_form');
    }

    public function getPublishableKey()
    {
        return $this->paymentMethod->getData('publishable_key');
    }

    public function getPublicData()
    {
        return [
            'publishable_key' => $this->getPublishableKey(),
        ];
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
