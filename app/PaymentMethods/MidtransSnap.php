<?php

namespace Kommercio\PaymentMethods;

use Carbon\Carbon;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Order\Payment;
use Kommercio\Models\PaymentMethod\PaymentMethod;
use Illuminate\Http\Request;

class MidtransSnap extends PaymentMethodAbstract implements PaymentMethodSettingFormInterface
{
    public function getSummary(Order $order, $options = null)
    {
        $view = ProjectHelper::getViewTemplate('frontend.order.payment_method.midtrans.snap');

        return view($view, ['order' => $order, 'paymentMethod' => $this])->render();
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

    public function getIs3ds()
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

    public function getServerKey()
    {
        return $this->paymentMethod->getData('server_key');
    }

    public function saveForm(Request $request)
    {

    }

    public function finalProcessPayment($options = null)
    {
        $order = $options['order'];

        if($order && $order->exists){
            $payment = Payment::createPayment($order, $invoice, Payment::STATUS_SUCCESS, $this->paymentMethod, $notes, $options);
        }
    }

    public function settingForm()
    {
        return ProjectHelper::getViewTemplate('backend.payment_method.Midtrans.Snap.additional_setting_form');
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