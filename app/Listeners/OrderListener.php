<?php

namespace Kommercio\Listeners;

use Illuminate\Support\Facades\Event;
use Kommercio\Events\CouponEvent;
use Kommercio\Events\OrderEvent;
use Kommercio\Facades\EmailHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Order\Order;
use Kommercio\Models\PaymentMethod\PaymentMethod;
use Kommercio\Models\RewardPoint\RewardPointTransaction;

class OrderListener
{
    protected $order;

    /**
     * Handle the event.
     *
     * @param  OrderUpdate  $event
     * @return void
     */
    public function handle(OrderEvent $event)
    {
        $order = $event->order;

        if($event->type == 'before_order_placed'){
            $this->beforeOrderPlaced($order);
        }elseif($event->type == 'customer_place_order'){
            $this->placeOrder($order);
        }elseif($event->type == 'internal_place_order'){
            $this->placeOrder($order, true);
        }elseif($event->type == 'shipping_method_changed'){
            $this->shippingMethodChanged($order);
        }elseif($event->type == 'process_payment'){
            $this->processPayment($order);
        }elseif($event->type == 'placed_order_updated'){
            $this->placedOrderUpdated($order);
        }
    }

    protected function beforeOrderPlaced(Order $order)
    {

    }

    protected function placeOrder(Order $order, $internal = false)
    {
        if(!$internal){
            if(ProjectHelper::isFeatureEnabled('customer.reward_points')){
                $existingReviewRewardPoints = $order->rewardPointTransactions()->where('status', RewardPointTransaction::STATUS_REVIEW)->get();
                foreach($existingReviewRewardPoints as $existingReviewRewardPoint){
                    $existingReviewRewardPoint->delete();
                }

                $order->addRewardPoint();
            }

            $subject = 'There is new order #'.$order->reference;

            $orderEmail = ProjectHelper::getConfig('contacts.order.email');

            EmailHelper::sendMail($orderEmail, $subject, 'order.admin_new_order', ['order' => $order], 'general');
        }
    }

    protected function placedOrderUpdated(Order $order)
    {

    }

    protected function shippingMethodChanged(Order $order)
    {

    }

    protected function processPayment(Order $order)
    {
        $paymentMethod = PaymentMethod::find($order->payment_method_id);

        if($paymentMethod){
            $paymentMethod->getProcessor()->processPayment();
        }
    }
}
