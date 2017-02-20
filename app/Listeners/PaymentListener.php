<?php

namespace Kommercio\Listeners;

use Kommercio\Events\OrderEvent;
use Kommercio\Events\PaymentEvent;
use Kommercio\Facades\EmailHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Order\Invoice;
use Kommercio\Models\Order\Payment;
use Kommercio\Models\PaymentMethod\PaymentMethod;
use Kommercio\Models\RewardPoint\RewardPointTransaction;

class PaymentListener
{
    protected $payment;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Payment $payment)
    {
        $this->order = $payment;
    }

    /**
     * Handle the event.
     *
     * @param  OrderUpdate  $event
     * @return void
     */
    public function handle(PaymentEvent $event)
    {
        $payment = $event->payment;

        if($event->type == 'accept'){
            $this->paymentAccepted($payment);
        }
    }

    protected function paymentAccepted(Payment $payment)
    {
        if($payment->order->getOutstandingAmount() <= 0){
            if(ProjectHelper::isFeatureEnabled('customer.reward_points')){
                $payment->order->addRewardPoint([
                    'status' => RewardPointTransaction::STATUS_APPROVED
                ]);
            }

            if($payment->invoice->status == Invoice::STATUS_UNPAID && $payment->amount >= $payment->invoice->total){
                $payment->invoice->markAsPaid();
            }
        }
    }
}
