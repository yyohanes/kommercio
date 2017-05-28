<?php

namespace Kommercio\Http\Controllers\Backend\Sales;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Kommercio\Events\OrderUpdate;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Facades\OrderHelper;
use Kommercio\Facades\PriceFormatter;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Order\Invoice;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Order\Payment;
use Kommercio\Models\PaymentMethod\PaymentMethod;

class PaymentController extends Controller{
    public function orderPaymentIndex($order_id)
    {
        $order = Order::findOrFail($order_id);

        $payments = $order->payments;

        $index = view('backend.order.payments.index', [
            'payments' => $payments
        ])->render();

        return response()->json([
            'html' => $index,
            '_token' => csrf_token()
        ]);
    }

    public function orderPaymentForm($order_id)
    {
        $payment = new Payment();
        $order = Order::findOrFail($order_id);

        $currencyOptions = CurrencyHelper::getCurrencyOptions();

        $paymentMethods = PaymentMethod::getPaymentMethods([
            'order' => $order
        ]);

        $paymentMethodOptions = [];
        foreach($paymentMethods as $paymentMethod){
            $paymentMethodOptions[$paymentMethod->id] = $paymentMethod->name;
        }

        $invoiceOptions = $order->invoices->pluck('reference', 'id')->all();

        $form = view('backend.order.payments.form', [
            'payment' => $payment,
            'order' => $order,
            'currencyOptions' => $currencyOptions,
            'paymentMethodOptions' => $paymentMethodOptions,
            'outstanding' => $order->getOutstandingAmount(),
            'invoiceOptions' => $invoiceOptions
        ])->render();

        return response()->json([
            'html' => $form,
            '_token' => csrf_token()
        ]);
    }

    public function orderPaymentSave(Request $request, $order_id)
    {
        $order = Order::findOrFail($order_id);

        $rules = [
            'payment.invoice_id' => 'required|in:'.implode(',', $order->invoices->pluck('id')->all()),
            'payment.payment_method_id' => 'required',
            'payment.amount' => 'required|numeric|min:0',
            'payment.currency' => 'required',
            'payment.notes' => ''
        ];

        $this->validate($request, $rules);

        $payment = new Payment();
        $payment->fill($request->input('payment'));
        $payment->order()->associate($order);
        $payment->status = Payment::STATUS_PENDING;
        $payment->payment_date = Carbon::now();
        $payment->save();

        if($request->has('attachments')){
            foreach($request->input('attachments', []) as $idx=>$image){
                $images[$image] = [
                    'type' => 'attachment',
                ];
            }
            $payment->attachMedia($images, 'attachment');
        }

        return response()->json([
            'result' => 'success',
            'message' => 'Payment with amount of '.PriceFormatter::formatNumber($payment->amount, $payment->currency).' is successfully entered.'
        ]);
    }

    public function process(Request $request, $process, $id)
    {
        $payment = Payment::findOrFail($id);

        if ($request->isMethod('GET')) {
            switch ($process) {
                case 'void':
                    $processForm = 'void_form';
                    break;
                case 'accept':
                    $processForm = 'accept_form';
                    break;
                default:
                    return response('No process is selected.');
                    break;
            }

            return view('backend.order.payments.process.' . $processForm, [
                'payment' => $payment,
                'backUrl' => route('backend.sales.order.view', ['id' => $payment->order->id]).'#tab_payments'
            ]);
        } else {
            $rules = [];
            $status = Payment::STATUS_SUCCESS;

            switch ($process) {
                case 'void':
                    $status = Payment::STATUS_VOID;
                    $rules['reason'] = 'required';
                    $message = 'Payment has been set to <span class="label bg-' . OrderHelper::getPaymentStatusLabelClass($payment->status) . ' bg-font-' . OrderHelper::getPaymentStatusLabelClass($payment->status) . '">Void.</span>';
                    break;
                case 'accept':
                    $status = Payment::STATUS_SUCCESS;

                    if($request->input('process_order', 0)){
                        $originalStatus = $payment->order->status;

                        $payment->order->status = Order::STATUS_PROCESSING;
                        $payment->order->save();

                        Event::fire(new OrderUpdate($payment->order, $originalStatus, $request->input('send_notification')));
                    }

                    $message = 'Payment has been set to <span class="label bg-' . OrderHelper::getPaymentStatusLabelClass($payment->status) . ' bg-font-' . OrderHelper::getPaymentStatusLabelClass($payment->status) . '">Success.</span>';
                    break;
                default:
                    $message = 'No process has been done.';
                    break;
            }

            $validator = Validator::make($request->all(), $rules);
            if($validator->fails()){
                return redirect(route('backend.sales.order.view', ['id' => $payment->order->id]).'#tab_payments')->withErrors($validator);
            }

            $payment->changeStatus($status, $request->input('reason'), Auth::user()->email);

            return redirect($request->input('backUrl', route('backend.sales.order.view', ['id' => $payment->order->id])))->with('success', [$message]);
        }
    }
}