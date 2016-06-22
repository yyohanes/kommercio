<?php

namespace Kommercio\Listeners;

use Illuminate\Http\Request;
use Kommercio\Events\OrderUpdate;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Kommercio\Facades\EmailHelper;
use Kommercio\Facades\OrderHelper;
use Kommercio\Models\Order\Order;

class OrderUpdateListener
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

    public function onPlacedOrder(OrderUpdate $event)
    {
        OrderHelper::saveOrderComment('Order is placed.', 'confirmation', $event->order, $this->request->user());

        if($event->notify_customer){
            $subject = 'Thank you for your order #'.$event->order->reference;
            EmailHelper::sendMail($event->order->billingProfile->email, $subject, 'order.confirmation', ['order' => $event->order], 'order');
        }
    }

    public function onProcessingOrder(OrderUpdate $event)
    {
        OrderHelper::saveOrderComment('Order is processed.', 'processing', $event->order, $this->request->user());

        if($event->notify_customer){
            $subject = 'We are processing your order #'.$event->order->reference;
            EmailHelper::sendMail($event->order->billingProfile->email, $subject, 'order.processing', ['order' => $event->order], 'order');
        }
    }

    public function onCompletedOrder(OrderUpdate $event)
    {
        OrderHelper::saveOrderComment('Order is completed.', 'completed', $event->order, $this->request->user());

        if($event->notify_customer){
            $subject = 'Your order #'.$event->order->reference.' is completed';
            EmailHelper::sendMail($event->order->billingProfile->email, $subject, 'order.completed', ['order' => $event->order], 'order');
        }
    }

    public function onCancelledOrder(OrderUpdate $event)
    {
        OrderHelper::saveOrderComment('Order is cancelled.', 'cancelled', $event->order, $this->request->user());

        if($event->notify_customer){
            $subject = 'Your order #'.$event->order->reference.' is cancelled';
            EmailHelper::sendMail($event->order->billingProfile->email, $subject, 'order.cancelled', ['order' => $event->order], 'order');
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
