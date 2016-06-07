<?php

namespace Kommercio\Listeners;

use Kommercio\Events\OrderUpdate;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Kommercio\Facades\EmailHelper;
use Kommercio\Models\Order\Order;

class OrderUpdateListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function onPlacedOrder(OrderUpdate $event)
    {
        if($event->notify_customer){
            $subject = 'Thank you for your order #'.$event->order->reference;
            EmailHelper::sendMail($event->order->billingProfile->email, $subject, 'order.confirmation', ['order' => $event->order]);
        }
    }

    public function onProcessingOrder(OrderUpdate $event)
    {
        if($event->notify_customer){
            $subject = 'We are processing your order #'.$event->order->reference;
            EmailHelper::sendMail($event->order->billingProfile->email, $subject, 'order.processing', ['order' => $event->order]);
        }
    }

    public function onCompletedOrder(OrderUpdate $event)
    {
        if($event->notify_customer){
            $subject = 'Your order #'.$event->order->reference.' is completed';
            EmailHelper::sendMail($event->order->billingProfile->email, $subject, 'order.completed', ['order' => $event->order]);
        }
    }

    public function onCancelledOrder(OrderUpdate $event)
    {
        if($event->notify_customer){
            $subject = 'Your order #'.$event->order->reference.' is cancelled';
            EmailHelper::sendMail($event->order->billingProfile->email, $subject, 'order.cancelled', ['order' => $event->order]);
        }
    }

    /**
     * Handle the event.
     *
     * @param  OrderUpdate  $event
     * @return void
     */
    public function handle(OrderUpdate $event)
    {
        $order = $event->order;

        //Init profiles
        $order->billingProfile->fillDetails();
        $order->shippingProfile->fillDetails();

        if($order->status != $event->originalStatus){
            if($order->status == Order::STATUS_PENDING){
                $this->onPlacedOrder($event);
            }elseif($order->status == Order::STATUS_PROCESSING){
                $this->onProcessingOrder($event);
            }elseif($order->status == Order::STATUS_COMPLETED){
                $this->onCompletedOrder($event);
            }elseif($order->status == Order::STATUS_CANCELLED){
                $this->onCancelledOrder($event);
            }
        }
    }
}
