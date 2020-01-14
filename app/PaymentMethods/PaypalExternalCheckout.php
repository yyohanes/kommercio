<?php

namespace Kommercio\PaymentMethods;

use Illuminate\Http\JsonResponse;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Order\Order;
use Illuminate\Http\Request;
use Kommercio\Models\Order\Payment;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\FlowConfig;
use PayPal\Api\InputFields;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment as PaypalPayment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Presentation;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\WebProfile;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;

class PaypalExternalCheckout extends PaymentMethodAbstract implements PaymentMethodSettingFormInterface, ExternalPaymentMethodInterface
{
    private $_apiContext;

    /**
     * @inheritdoc
     */
    public function getSummary(Order $order, $options = null)
    {
        $view = ProjectHelper::getViewTemplate('frontend.order.payment_method.paypal.external_checkout');

        return view($view, ['order' => $order, 'paymentMethod' => $this])->render();
    }

    public function saveForm(Request $request)
    {
        $apiContext = $this->getApiContext();

        $new = true;
        $webProfile = null;
        $currentList = WebProfile::get_list($apiContext);

        //Find profile that ends with _kommercio
        foreach($currentList as $profile){
            if(strpos($profile->getName(), '_kommercio') !== false){
                $webProfile = $profile;
                $new = false;
                break;
            }
        }

        $name = ProjectHelper::getConfig('project_machine_name') . '_kommercio';

        if(empty($webProfile)){
            $flowConfig = new FlowConfig();
            $presentation = new Presentation();
            $inputFields = new InputFields();

            $webProfile = new WebProfile();
            $webProfile
                ->setName($name)
                ->setFlowConfig($flowConfig)
                ->setPresentation($presentation)
                ->setInputFields($inputFields)
                ->setTemporary(false);
        }else{
            $flowConfig = $webProfile->getFlowConfig();
            $presentation = $webProfile->getPresentation();
            $inputFields = $webProfile->getInputFields();
        }

        $flowConfig->setLandingPageType('Billing');
        $flowConfig->setUserAction('commit');
        $flowConfig->setReturnUriHttpMethod('GET');

        $presentation
            ->setBrandName(ProjectHelper::getConfig('client_name'))
            ->setLocaleCode('US')
            ->setReturnUrlLabel('Return')
            ->setNoteToSellerLabel('Thank you for shopping with us!');

        $inputFields
            ->setAllowNote(false)
            ->setNoShipping(1)
            ->setAddressOverride(1);

        if($new){
            try{
                $response = $webProfile->create($apiContext);
                $this->paymentMethod->saveData(['web_experience_profile_id' => $response->getId()]);
            } catch (PayPalConnectionException $e) {
                \Log::info($e->getData());
            }
        }else{
            try{
                $webProfile->update($apiContext);
                $this->paymentMethod->saveData(['web_experience_profile_id' => $webProfile->getId()]);
            } catch (PayPalConnectionException $e) {
                \Log::info($e->getData());
            }
        }

        $this->paymentMethod->save();
    }

    public function getPaymentForm(Payment $payment, $options = null) {
        $order = $payment->order;

        $view = ProjectHelper::getViewTemplate('frontend.order.payment_method.paypal.external_checkout_form');

        try {
            $return = view($view, [
                'payment' => $payment,
                'order' => $order,
                'paymentMethod' => $this,
                'redirectUrl' => $payment->getData('paypal_payment_url'),
                'redirectBackUrl' => route('frontend.order.onepage_checkout'),
            ])->render();
        } catch (\Throwable $e) {
            \Log::error($e);

            $return = null;
        }

        return $return;
    }

    public function finalProcessPayment($options = null) {
        $payment = null;
        $order = $options['order'];

        if($order && $order->exists){
            try {
                $payment = Payment::createIniatePayment($order);
            } catch (\Throwable $e) {
                \Log::error($e->getMessage());
            }
        }

        if (!$payment) {
            return [
                'There is an error. Please try again.'
            ];
        }

        try {
            $currency = CurrencyHelper::getCurrency($order->currency);
            $currencyIso = $currency['iso'];

            $payer = new Payer();
            $payer->setPaymentMethod('paypal');

            $itemList = new ItemList();

            foreach($order->lineItems as $lineitem){
                //Product
                if($lineitem->isProduct){
                    $item = new Item();
                    $item->setName($lineitem->name)
                        ->setCurrency($currencyIso)
                        ->setQuantity($lineitem->quantity)
                        ->setSku($lineitem->product->sku)
                        ->setTax($lineitem->tax_total)
                        ->setPrice($lineitem->calculateNet(false));

                    $itemList->addItem($item);
                }else if(!$lineitem->isTax){
                    $item = new Item();
                    $item->setName($lineitem->name)
                        ->setCurrency($currencyIso)
                        ->setQuantity($lineitem->quantity)
                        ->setTax($lineitem->tax_total)
                        ->setPrice($lineitem->calculateNet(false));

                    $itemList->addItem($item);
                }
            }

            $details = new Details();
            $details
                ->setTax($order->calculateTaxTotal())
                ->setSubtotal($order->calculateSubtotal() + $order->calculateShippingTotal() + $order->calculateDiscountTotal());

            $amount = new Amount();
            $amount
                ->setCurrency($currencyIso)
                ->setDetails($details)
                ->setTotal($order->calculateTotal());

            $transaction = new Transaction();
            $transaction->setAmount($amount)
                ->setItemList($itemList)
                ->setInvoiceNumber($payment->generateExternalReference());

            $redirectUrls = new RedirectUrls();
            $redirectUrls
                ->setReturnUrl(route('frontend.order.checkout.payment.notify', [
                    'payment_id' => $payment->public_id,
                    'status' => 'success',
                ]))
                ->setCancelUrl(route('frontend.order.checkout.payment.notify', [
                    'payment_id' => $payment->public_id,
                    'status' => 'cancel',
                ]));

            $paypalPayment = new PaypalPayment();
            $paypalPayment->setIntent('sale')
                ->setPayer($payer)
                ->setRedirectUrls($redirectUrls)
                ->setTransactions(array($transaction));
            \Log::info($paypalPayment->toJSON());

            if($this->paymentMethod->hasData('web_experience_profile_id')){
                $paypalPayment->setExperienceProfileId($this->paymentMethod->getData('web_experience_profile_id'));
            }

            $paypalPayment->create($this->getApiContext());

            $payment->saveData([
                'paypal_payment_url' => $paypalPayment->getApprovalLink(),
            ], true);
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());

            return [
                'There is an error. Please try again.'
            ];
        }

        return $payment;
    }

    /**
     * @param Request $request
     * @param Payment $payment
     * @return Payment
     * @throws \Throwable
     */
    public function handleExternalNotification(Request $request, Payment $payment) {
        $options = [];
        $status = $request->get('status');

        if($status == 'success'){
            $paymentId = $request->input('paymentId');

            try {
                $paypalPayment = PaypalPayment::get($paymentId, $this->getApiContext());

                $payment = Payment::getPaymentFromExternal($paypalPayment->getTransactions()[0]->getInvoiceNumber());
                $order = $payment->order;
            } catch (\Throwable $e) {
                throw $e;
            }

            $currency = CurrencyHelper::getCurrency($order->currency);
            $currencyIso = $currency['iso'];

            $payerId = $request->input('PayerID');
            $execution = new PaymentExecution();
            $execution->setPayerId($payerId);

            $transaction = new Transaction();
            $amount = new Amount();
            $details = new Details();

            $details
                ->setShipping($order->calculateShippingTotal())
                ->setTax($order->calculateTaxTotal())
                ->setSubtotal($order->calculateSubtotal() + $order->calculateDiscountTotal());

            $amount
                ->setCurrency($currencyIso)
                ->setDetails($details)
                ->setTotal($order->calculateTotal());

            $transaction->setAmount($amount);

            $execution->addTransaction($transaction);

            $newStatus = Payment::STATUS_FAILED;

            try{
                $paypalPayment->execute($execution, $this->getApiContext());

                if ($paypalPayment->getState() !== 'approved') {
                    throw new \Exception('Payment is not approved.');
                }

                $options['response'] = json_encode($paypalPayment->toArray(), JSON_PRETTY_PRINT);

                $newStatus = Payment::STATUS_SUCCESS;

                $return = [
                    'result' => 1,
                    'payment' => $paypalPayment->toJSON()
                ];
            } catch (\Throwable $e) {
                throw $e;
            }
        }else{
            $newStatus = Payment::STATUS_FAILED;
        }

        $note = 'Status change from '.Payment::getStatusOptions($payment->status).' to '.Payment::getStatusOptions($newStatus);
        $payment->changeStatus($newStatus, $note, 'Paypal Notification', $options);

        return $payment;
    }

    public function settingForm()
    {
        return ProjectHelper::getViewTemplate('backend.payment_method.Paypal.ExternalCheckout.additional_setting_form');
    }

    public function getIsProduction()
    {
        return $this->paymentMethod->getData('is_production', false);
    }

    public function getEnvironment()
    {
        return $this->getIsProduction()?'production':'sandbox';
    }

    public function getEmail()
    {
        return $this->paymentMethod->getData('email');
    }

    public function getClientId()
    {
        return $this->paymentMethod->getData('client_id');
    }

    public function getSecretKey()
    {
        return $this->paymentMethod->getData('secret_key');
    }

    public function isExternal() {
        return true;
    }

    public function getApiContext()
    {
        if(!isset($this->_apiContext)){
            $this->_apiContext = new ApiContext(
                new OAuthTokenCredential(
                    $this->getClientId(),
                    $this->getSecretKey()
                )
            );

            if($this->getEnvironment() == 'production'){
                $this->_apiContext->setConfig([
                    'mode' => 'live',
                    'log.LogEnabled' => true,
                    'log.FileName' => storage_path('logs/PayPal.log'),
                    'log.LogLevel' => 'INFO', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
                    'cache.enabled' => true,
                ]);
            }else{
                $this->_apiContext->setConfig([
                    'mode' => 'sandbox',
                    'log.LogEnabled' => true,
                    'log.FileName' => storage_path('logs/PayPal.log'),
                    'log.LogLevel' => 'DEBUG', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
                    'cache.enabled' => true,
                ]);
            }
        }

        return $this->_apiContext;
    }

    //Statics
    public static function additionalSettingValidation(Request $request)
    {
        return [
            'data.email' => 'required|email',
            'data.secret_key' => 'required',
            'data.client_id' => 'required'
        ];
    }
}
