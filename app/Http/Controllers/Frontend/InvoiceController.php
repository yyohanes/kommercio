<?php

namespace Kommercio\Http\Controllers\Frontend;

use Illuminate\Http\Request;

use Kommercio\Facades\LanguageHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Http\Requests;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Order\Invoice;
use Kommercio\Models\Order\Order;
use Kommercio\Models\PaymentMethod\PaymentMethod;

class InvoiceController extends Controller
{
    public function view($public_id)
    {
        $invoice = Invoice::findPublic($public_id);
        $order = $invoice->order;

        $paymentMethods = $this->getPaymentMethods($order);

        $viewName = ProjectHelper::getViewTemplate('frontend.order.invoice.view');

        return view($viewName, [
            'invoice' => $invoice,
            'order' => $order,
            'paymentMethods' => $paymentMethods
        ]);
    }

    /**
     * Process the payment. Payment processing follows one-page-checkout process.
     * Thus, so we don't need to create different payment process for invoice payment
     * @param Request $request
     * @param $public_id
     * @return $this
     */
    public function payment(Request $request, $public_id)
    {
        $invoice = Invoice::findPublic($public_id);

        if(!$invoice){
            return redirect()->back()->withErrors(['Invoice not found.']);
        }

        if(!$request->isMethod('POST')){
            return redirect()->route('frontend.order.invoice.view', ['public_id' => $public_id]);
        }

        $order = $invoice->order;

        //First validation to validate payment method
        $rules = [
            'payment_method' => 'required|exists:payment_methods,id'
        ];
        $this->validate($request, $rules);

        $paymentMethod = PaymentMethod::find($request->input('payment_method'));

        //Second validation to validate payment data
        $rules += $paymentMethod->getProcessor()->getValidationRules();
        $this->validate($request, $rules);

        //First payment process
        $paymentMethod->getProcessor()->processPayment([
            'order' => $order,
            'request' => $request
        ]);

        //Save order after processed by payment processor
        $order->save();

        //Final process payment based on first processing
        $errors = null;

        $paymentResponse = $this->processFinalPayment($invoice, $order, $paymentMethod, $request);

        if(is_array($paymentResponse)){
            $errors = $paymentResponse;
        }

        if($errors){
            return redirect()->back()->withErrors($errors);
        }

        return redirect()->back()->with('success', [trans(LanguageHelper::getTranslationKey('frontend.order.invoice.payment.success'))]);
    }

    protected function getPaymentMethods($order)
    {
        $paymentMethods = PaymentMethod::getPaymentMethods([
            'frontend' => true,
            'order' => $order,
        ]);

        return $paymentMethods;
    }

    protected function processFinalPayment(Invoice $invoice, Order $order, PaymentMethod $paymentMethod, Request $request)
    {
        return $paymentMethod->getProcessor()->finalProcessPayment([
            'order' => $order,
            'request' => $request,
            'invoice' => $invoice
        ]);
    }
}
