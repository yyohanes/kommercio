<?php

namespace Kommercio\PaymentMethods;

use Carbon\Carbon;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Order\Payment;
use Kommercio\Models\PaymentMethod\PaymentMethod;
use Illuminate\Http\Request;

class MidtransSnap implements PaymentMethodInterface, PaymentMethodSettingFormInterface
{
    protected $paymentMethod;

    public function validate($options = null)
    {
        $valid = TRUE;

        if(isset($options['frontend'])){
            //$valid = $options['frontend'];
        }

        return $valid;
    }

    public function isExternalCheckout()
    {
        return true;
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

    public function stepPaymentMethodValidation($options = null)
    {
        return true;
    }

    public function processPayment($options = null)
    {

    }

    public function paymentMethodValidation($options = null)
    {
        $order = $options['order'];

        \Stripe\Stripe::setApiKey($this->getSecretKey());

        try{
            $token = \Stripe\Token::retrieve($order->getData('midtransSnapToken', null));
            return !$token->used;
        }catch(\Exception $e){
            return false;
        }
    }

    public function finalProcessPayment($options = null)
    {
        $order = $options['order'];

        if($order && $order->exists){
            \Veritrans_Config::$serverKey = $this->getServerKey();
            \Veritrans_Config::$isProduction = $this->getIsProduction();
            \Veritrans_Config::$is3ds = $this->getIs3DS();

            $transaction_details = array(
                'order_id' => $order->id.time(),
                'gross_amount' => $order->total, // no decimal allowed for creditcard
            );

            $item1_details = array(
                'id' => 'a1',
                'price' => 18000,
                'quantity' => 3,
                'name' => "Apple"
            );

            $item2_details = array(
                'id' => 'a2',
                'price' => 20000,
                'quantity' => 2,
                'name' => "Orange"
            );

            $item_details = array ($item1_details, $item2_details);

            // Optional
            $billing_address = array(
                'first_name'    => "Andri",
                'last_name'     => "Litani",
                'address'       => "Mangga 20",
                'city'          => "Jakarta",
                'postal_code'   => "16602",
                'phone'         => "081122334455",
                'country_code'  => 'IDN'
            );

            // Optional
            $shipping_address = array(
                'first_name'    => "Obet",
                'last_name'     => "Supriadi",
                'address'       => "Manggis 90",
                'city'          => "Jakarta",
                'postal_code'   => "16601",
                'phone'         => "08113366345",
                'country_code'  => 'IDN'
            );

            // Optional
            $customer_details = array(
                'first_name'    => "Andri",
                'last_name'     => "Litani",
                'email'         => "andri@litani.com",
                'phone'         => "081122334455",
                'billing_address'  => $billing_address,
                'shipping_address' => $shipping_address
            );

            // Fill transaction details
            $transaction = array(
                //'enabled_payments' => $enable_payments,
                'transaction_details' => $transaction_details,
                'customer_details' => $customer_details,
                'item_details' => $item_details,
            );

            try{
                $snapToken = \Veritrans_Snap::getSnapToken($transaction);

                $order->saveData(['midtransSnapToken' => $snapToken]);

                /*$paymentData = [
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
                $payment->save();*/
            }catch (\Exception $e){
                \Log::info($e);
            }
        }
    }

    public function getIsProduction()
    {
        return $this->paymentMethod->getData('is_production', false);
    }

    public function getEnvironment()
    {
        return $this->getIsProduction()?'production':'sandbox';
    }

    public function getJsUrl()
    {
        return $this->getIsProduction()?'https://app.midtrans.com/snap/snap.js':'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    public function getIs3DS()
    {
        return $this->paymentMethod->getData('3ds', true);
    }

    public function getMerchantId()
    {
        return $this->paymentMethod->getData('merchant_id');
    }

    public function getClientKey()
    {
        return $this->paymentMethod->getData('client_key');
    }

    protected function getServerKey()
    {
        return $this->paymentMethod->getData('server_key');
    }

    public function settingForm()
    {
        return ProjectHelper::getViewTemplate('backend.payment_method.midtrans.snap.additional_setting_form');
    }

    //Statics
    public static function additionalSettingValidation(Request $request)
    {
        return [
            'data.is_production' => 'required|boolean',
            'data.merchant_id' => 'required',
            'data.client_key' => 'required',
            'data.server_key' => 'required',
        ];
    }
}