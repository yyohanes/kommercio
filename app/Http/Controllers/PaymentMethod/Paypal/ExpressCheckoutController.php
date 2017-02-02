<?php

namespace Kommercio\Http\Controllers\PaymentMethod\Paypal;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Order\Order;
use Kommercio\Models\PaymentMethod\PaymentMethod;
use Kommercio\Models\Order\Payment as KommercioPayment;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;

class ExpressCheckoutController extends Controller
{
    protected $paymentMethod;
    protected $apiContext;

    public function __construct()
    {
        $this->paymentMethod = PaymentMethod::where('class', 'PaypalExpressCheckout')->firstOrFail();

        $this->apiContext = new ApiContext(
            new OAuthTokenCredential(
                $this->paymentMethod->getProcessor()->getClientId(),
                $this->paymentMethod->getProcessor()->getSecretKey()
            )
        );

        if($this->getIsProduction()){
            $this->apiContext->setConfig([
                'mode' => 'live',
                'log.LogEnabled' => true,
                'log.FileName' => storage_path('logs/PayPal.log'),
                'log.LogLevel' => 'INFO', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
                'cache.enabled' => true,
            ]);
        }else{
            $this->apiContext->setConfig([
                'mode' => 'sandbox',
                'log.LogEnabled' => true,
                'log.FileName' => storage_path('logs/PayPal.log'),
                'log.LogLevel' => 'DEBUG', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
                'cache.enabled' => true,
            ]);
        }
    }

    public function create(Request $request)
    {
        $order = Order::findPublic($request->input('order_id'));

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
                    ->setPrice($lineitem->calculateNet());

                $itemList->addItem($item);
            }else if(!$lineitem->isShipping){
                $item = new Item();
                $item->setName($lineitem->name)
                    ->setCurrency($currencyIso)
                    ->setQuantity($lineitem->quantity)
                    //->setSku($productLineitem->product->sku)
                    ->setPrice($lineitem->calculateNet());

                $itemList->addItem($item);
            }
        }

        $details = new Details();
        $details
            ->setShipping($order->calculateShippingTotal())
            ->setTax($order->calculateTaxTotal())
            ->setSubtotal($order->calculateSubtotal());

        $amount = new Amount();
        $amount
            ->setCurrency($currencyIso)
            ->setDetails($details)
            ->setTotal($order->calculateTotal());

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setInvoiceNumber(uniqid());

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl(route('frontend.payment_method.paypal.express_checkout.execute', ['status' => 'success']))
            ->setCancelUrl(route('frontend.payment_method.paypal.express_checkout.execute', ['status' => 'cancel']));

        $payment = new Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions(array($transaction));

        if($this->paymentMethod->hasData('web_experience_profile_id')){
            $payment->setExperienceProfileId($this->paymentMethod->getData('web_experience_profile_id'));
        }

        try {
            $payment->create($this->apiContext);
        } catch (PayPalConnectionException $e) {
            $return = [
                'result' => 0,
                'message' => $e->getMessage()
            ];
        }

        $approvalUrl = $payment->getApprovalLink();

        $return = [
            'result' => 1,
            'paymentID' => $payment->getId()
        ];

        return new JsonResponse($return);
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

            try{
                $result = $payment->execute($execution, $this->apiContext);

                try {
                    $payment = Payment::get($paymentId, $this->apiContext);

                    $options['response'] = json_encode($payment->toArray(), JSON_PRETTY_PRINT);

                    $kommercioPayment = KommercioPayment::createPayment($order, null, KommercioPayment::STATUS_SUCCESS, $order->paymentMethod, null, $options);

                    $return = [
                        'result' => 1,
                        'payment' => $payment->toJSON()
                    ];
                } catch (PayPalConnectionException $e) {
                    \Log::info($e->getData());

                    $return = [
                        'result' => 0,
                        'message' => $e->getMessage()
                    ];
                }
            } catch (PayPalConnectionException $e) {
                \Log::info($e->getData());

                $return = [
                    'result' => 0,
                    'message' => $e->getMessage()
                ];
            }
        }else{
            \Log::info('fail');
            \Log::info($request);

            $return = [
                'result' => 0,
                'message' => 'You have cancelled the payment.'
            ];
        }

        return new JsonResponse($return);
    }
}
