<?php

namespace Kommercio\Http\Controllers\PaymentMethod\Stripe;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Order\Payment;
use Kommercio\Models\PaymentMethod\PaymentMethod;

class StripeController extends Controller
{
  public function notify (Request $request) {
    \Log::info($request->input());
  }
}