<?php

namespace Kommercio\Listeners;

use Illuminate\Http\Request;
use Kommercio\Events\OrderEvent;
use Kommercio\Events\PaymentEvent;
use Kommercio\Facades\EmailHelper;
use Kommercio\Facades\OrderHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Order\Invoice;
use Kommercio\Models\Order\OrderComment;
use Kommercio\Models\Order\Payment;
use Kommercio\Models\PaymentMethod\PaymentMethod;
use Kommercio\Models\RewardPoint\RewardPointTransaction;

class PaymentListener
{
    protected $request;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     *
     * @param  PaymentEvent  $event
     * @return void
     */
    public function handle(PaymentEvent $event)
    {
        $payment = $event->payment;

        if($event->type == 'accept'){
            $this->paymentAccepted($payment);
        }elseif($event->type == 'void'){
            $this->paymentVoided($payment, $event->params['note']);
        }
    }

    protected function paymentAccepted(Payment $payment)
    {
        OrderHelper::saveOrderComment('Payment received.', 'payment_received', $payment->order, $this->request->user(), OrderComment::TYPE_EXTERNAL_MEMO, [
            'payment_id' => $payment->id
        ]);

        if ($payment->order->getOutstandingAmount() <= 0) {
            if (ProjectHelper::isFeatureEnabled('customer.reward_points')) {
                $payment->order->addRewardPoint([
                    'status' => RewardPointTransaction::STATUS_APPROVED
                ]);
            }

            if ($payment->invoice->status == Invoice::STATUS_UNPAID && $payment->amount >= $payment->invoice->total) {
                $payment->invoice->markAsPaid();
            }
        }
    }

    protected function paymentVoided(Payment $payment, $reason)
    {
        OrderHelper::saveOrderComment('Payment is voided.', 'payment_voided', $payment->order, $this->request->user(), OrderComment::TYPE_EXTERNAL_MEMO, [
            'payment_id' => $payment->id,
            'reason' => $reason
        ]);
    }
}
