<?php

namespace Kommercio\Http\Controllers\PaymentMethod\Midtrans;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Order\Payment;
use Kommercio\Models\PaymentMethod\PaymentMethod;

class SnapController extends Controller
{
    protected $paymentMethod;

    public function __construct()
    {
        $this->paymentMethod = PaymentMethod::where('class', 'MidtransSnap')->firstOrFail();

        \Veritrans_Config::$serverKey = $this->paymentMethod->getProcessor()->getServerKey();
        \Veritrans_Config::$isProduction = $this->paymentMethod->getProcessor()->getIsProduction();
        \Veritrans_Config::$is3ds = $this->paymentMethod->getProcessor()->getIs3ds()?true:false;
    }

    public function token(Request $request)
    {
        $order = Order::findPublic($request->input('order_id'));

        $orderTotal = 0;

        $items = [];

        foreach($order->lineItems as $idx => $lineitem){
            $price = round($lineitem->calculateNet());
            $orderTotal += $price;

            $items[] = [
                'id' => $idx + 1,
                'price' => $price,
                'quantity' => $lineitem->quantity,
                'name' => $lineitem->name
            ];
        }

        //TODO: Add 3-letters country code to our Address DB
        $billing_address = array(
            'first_name'    => $order->shippingInformation->first_name,
            'last_name'     => $order->shippingInformation->last_name,
            'address'       => $order->shippingInformation->address_1,
            'city'          => $order->shippingInformation->city?$order->shippingInformation->city->name:'',
            'postal_code'   => $order->shippingInformation->postal_code,
            'phone'         => $order->shippingInformation->phone_number,
            'country_code'  => 'IDN'
        );

        $customer_details = array(
            'first_name'    => $order->shippingInformation->first_name,
            'last_name'     => $order->shippingInformation->last_name,
            'email'         => $order->customer->getProfile()->email,
            'phone'         => $order->shippingInformation->phone_number,
            'billing_address'  => $billing_address,
            'shipping_address' => $billing_address
        );

        $payment = Payment::createIniatePayment($order);

        $transaction_details = array(
            'order_id' => $payment->invoice->reference.'/'.$payment->id,
            'gross_amount' => $orderTotal,
        );

        //TODO: Enabled payments setting form on Backoffice
        //$enable_payments = array('credit_card','cimb_clicks','mandiri_clickpay','echannel');

        $enable_payments = array('credit_card');
        // Fill transaction details
        $transaction = array(
            'enabled_payments' => $enable_payments,
            'transaction_details' => $transaction_details,
            'customer_details' => $customer_details,
            'item_details' => $items,
        );

        try{
            $snapToken = \Veritrans_Snap::getSnapToken($transaction);

            $return = [
                'token' => $snapToken
            ];

            $code = 200;
        }catch (\Exception $e){
            $return = [
                'message' => $e->getMessage()
            ];

            $code = $e->getCode();
        }

        return new JsonResponse($return, $code);
    }

    public function notify(Request $request)
    {
        $rules = [
            'order_id' => 'required',
            'payment_type' => 'required',
            'gross_amount' => 'required',
        ];

        $orderId = $request->input('order_id');

        $explodedOrderId = explode('/', $orderId);
        $paymentId = array_pop($explodedOrderId);

        $payment = Payment::findOrFail($paymentId);

        $paymentType = $request->input('payment_type');
        $transactionStatus = $request->input('transaction_status');

        if($paymentType == 'credit_card'){
            if($transactionStatus == 'capture'){
                $note = 'Status change from '.Payment::getStatusOptions($payment->status).' to '.Payment::getStatusOptions(Payment::STATUS_SUCCESS);
                $data = [
                    'response' => json_encode($request->all(), JSON_PRETTY_PRINT)
                ];

                $payment->recordStatusChange(Payment::STATUS_SUCCESS, 'Midtrans Notification', $note, $data);
            }
        }else{
            if($transactionStatus == 'settlement'){

            }
        }
    }
}