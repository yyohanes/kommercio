<?php

namespace Kommercio\PaymentMethods;

use Illuminate\Http\Request;
use Kommercio\Models\Order\Payment;

interface ExternalPaymentMethodInterface
{
    public function getPaymentForm(Payment $payment, $options = null);
    public function handleExternalNotification(Request $request, Payment $payment);
}
