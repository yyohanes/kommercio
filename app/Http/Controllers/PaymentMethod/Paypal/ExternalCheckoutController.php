<?php

namespace Kommercio\Http\Controllers\PaymentMethod\Paypal;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Models\Order\Order;
use Kommercio\Models\PaymentMethod\PaymentMethod;
use Kommercio\Models\Order\Payment as KommercioPayment;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;
use PayPal\Exception\PayPalConnectionException;

class ExternalCheckoutController extends Controller
{
    protected $paymentMethod;
    protected $apiContext;

    public function __construct()
    {
        $this->paymentMethod = PaymentMethod::where('class', 'PaypalExternalCheckout')->firstOrFail();

        $this->apiContext = $this->paymentMethod->getProcessor()->getApiContext();
    }

    public function execute(Request $request, $status)
    {
        if($status == 'success'){
            $order = Order::findPublic($request->input('order_id'));

            $currency = CurrencyHelper::getCurrency($order->currency);
            $currencyIso = $currency['iso'];

            $paymentId = $request->input('paymentId');
            $payment = Payment::get($paymentId, $this->apiContext);

            $payerId = $request->input('PayerID');
            $execution = new PaymentExecution();
            $execution->setPayerId($payerId);

            $transaction = new Transaction();
            $amount = new Amount();
            $details = new Details();

            $details
                ->setShipping($order->calculateShippingTotal())
                ->setTax($order->calculateTaxTotal())
                ->setSubtotal($order->calculateSubtotal());

            $amount
                ->setCurrency($currencyIso)
                ->setDetails($details)
                ->setTotal($order->calculateTotal());

            $transaction->setAmount($amount);

            $execution->addTransaction($transaction);

            $newStatus = KommercioPayment::STATUS_FAILED;

            try{
                $result = $payment->execute($execution, $this->apiContext);

                try {
                    $payment = Payment::get($paymentId, $this->apiContext);
                    $kommercioPayment = KommercioPayment::getPaymentFromExternal($payment->getTransactions()[0]->getInvoiceNumber());

                    $options['response'] = json_encode($payment->toArray(), JSON_PRETTY_PRINT);

                    $newStatus = KommercioPayment::STATUS_SUCCESS;

                    $return = [
                        'result' => 1,
                        'payment' => $payment->toJSON()
                    ];
                } catch (PayPalConnectionException $e) {
                    \Log::error($e->getMessage());

                    $return = [
                        'result' => 0,
                        'message' => $e->getMessage()
                    ];
                }
            } catch (PayPalConnectionException $e) {
                \Log::error($e->getMessage());

                $return = [
                    'result' => 0,
                    'message' => $e->getMessage()
                ];
            }
        }else{
            $newStatus = KommercioPayment::STATUS_FAILED;

            $return = [
                'result' => 0,
                'message' => 'You have cancelled the payment.'
            ];
        }

        $note = 'Status change from '.KommercioPayment::getStatusOptions($kommercioPayment->status).' to '.KommercioPayment::getStatusOptions($newStatus);
        $kommercioPayment->changeStatus($newStatus, $note, 'Paypal Notification', $options);

        return new JsonResponse($return);
    }
}
