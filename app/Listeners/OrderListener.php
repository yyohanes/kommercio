<?php

namespace Kommercio\Listeners;

use Illuminate\Support\Facades\Event;
use Kommercio\Events\CouponEvent;
use Kommercio\Events\OrderEvent;
use Kommercio\Facades\EmailHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Order\Invoice;
use Kommercio\Models\Order\Order;
use Kommercio\Models\PaymentMethod\PaymentMethod;
use Kommercio\Models\RewardPoint\RewardPointTransaction;

class OrderListener
{
    protected $order;

    /**
     * Handle the event.
     *
     * @param  OrderEvent  $event
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
        // Generate invoice if not yet created. Possible by payment
        if($order->invoices->count() < 1){
            Invoice::createInvoice($order);
            $order->load('invoices');
        }

        // Give shipping method a chance to handle new order
        try {
            $order->getShippingMethod()->getProcessor()->handleNewOrder($order);
        } catch (\Exception $e) {
            \Log::error($e);
        }

        if(!$internal){
            if(ProjectHelper::isFeatureEnabled('customer.reward_points')){
                $existingReviewRewardPoints = $order->rewardPointTransactions()->where('status', RewardPointTransaction::STATUS_REVIEW)->get();
                foreach($existingReviewRewardPoints as $existingReviewRewardPoint){
                    $existingReviewRewardPoint->delete();
                }

                $order->addRewardPoint();
            }

            if (ProjectHelper::getConfig('order_options.new_order_notification', true)) {
                $subject = 'There is new order #'.$order->reference;

                $orderEmail = $order->store->getData('contacts.order.email');
                $orderEmail = empty($orderEmail)?ProjectHelper::getConfig('contacts.order.email'):$orderEmail;

                EmailHelper::sendMail($orderEmail, $subject, 'order.admin_new_order', ['order' => $order, 'store' => $order->store], 'general');
            }
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
