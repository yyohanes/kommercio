<?php

namespace Kommercio\Listeners;

use Illuminate\Http\Request;
use Kommercio\Events\OrderUpdate;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Kommercio\Facades\EmailHelper;
use Kommercio\Facades\OrderHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Order\Order;
use Kommercio\Models\RewardPoint\RewardPointTransaction;

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
            OrderHelper::sendOrderEmail($event->order, 'confirmation');
        }
    }

    public function onProcessingOrder(OrderUpdate $event)
    {
        OrderHelper::saveOrderComment('Order is processed.', 'processing', $event->order, $this->request->user());

        if($event->notify_customer){
            OrderHelper::sendOrderEmail($event->order, 'processing');
        }
    }

    public function onShippedOrder(OrderUpdate $event)
    {
        OrderHelper::saveOrderComment('Order is shipped.', 'shipped', $event->order, $this->request->user());

        if($event->notify_customer){
            OrderHelper::sendOrderEmail($event->order, 'shipped');
        }
    }

    public function onCompletedOrder(OrderUpdate $event)
    {
        OrderHelper::saveOrderComment('Order is completed.', 'completed', $event->order, $this->request->user());

        if($event->notify_customer){
            OrderHelper::sendOrderEmail($event->order, 'completed');
        }
    }

    public function onCancelledOrder(OrderUpdate $event)
    {
        OrderHelper::saveOrderComment('Order is cancelled. Reason: '.$this->request->input('notes'), 'cancelled', $event->order, $this->request->user());

        if($event->notify_customer){
            OrderHelper::sendOrderEmail($event->order, 'cancelled');
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
            }elseif($order->status == Order::STATUS_SHIPPED){
                $this->onShippedOrder($event);
            }elseif($order->status == Order::STATUS_COMPLETED){
                $this->onCompletedOrder($event);
            }elseif($order->status == Order::STATUS_CANCELLED){
                $this->onCancelledOrder($event);
            }
        }
    }
}
