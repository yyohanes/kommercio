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
use Kommercio\Models\Order\OrderComment;
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
        OrderHelper::saveOrderComment($event->overrideInternalMessage ? : 'Order is placed.', 'confirmation', $event->order, $this->request->user());
        OrderHelper::saveOrderComment($event->overrideExternalMessage ? : 'Order is placed.', 'confirmation', $event->order, $this->request->user(), OrderComment::TYPE_EXTERNAL_MEMO);

        if($event->notify_customer){
            OrderHelper::sendOrderEmail($event->order, 'confirmation');
        }
    }

    public function onProcessingOrder(OrderUpdate $event)
    {
        OrderHelper::saveOrderComment($event->overrideInternalMessage ? : 'Order is processed.', 'processing', $event->order, $this->request->user());
        OrderHelper::saveOrderComment($event->overrideExternalMessage ? : 'Order is processed.', 'processing', $event->order, $this->request->user(), OrderComment::TYPE_EXTERNAL_MEMO);

        if($event->notify_customer){
            OrderHelper::sendOrderEmail($event->order, 'processing');
        }
    }

    public function onCompletedOrder(OrderUpdate $event)
    {
        OrderHelper::saveOrderComment($event->overrideInternalMessage ? : 'Order is completed.', 'completed', $event->order, $this->request->user());
        OrderHelper::saveOrderComment($event->overrideExternalMessage ? : 'Order is completed.', 'completed', $event->order, $this->request->user(), OrderComment::TYPE_EXTERNAL_MEMO);

        if($event->notify_customer){
            OrderHelper::sendOrderEmail($event->order, 'completed');
        }
    }

    public function onCancelledOrder(OrderUpdate $event)
    {
        OrderHelper::saveOrderComment($event->overrideInternalMessage ? : 'Order is cancelled. Reason: '.$this->request->input('notes'), 'cancelled', $event->order, $this->request->user());
        OrderHelper::saveOrderComment($event->overrideExternalMessage ? : 'Order is cancelled.', 'cancelled', $event->order, $this->request->user(), OrderComment::TYPE_EXTERNAL_MEMO, [
            'reason' => $this->request->input('notes')
        ]);

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
            }elseif($order->status == Order::STATUS_COMPLETED){
                $this->onCompletedOrder($event);
            }elseif($order->status == Order::STATUS_CANCELLED){
                $this->onCancelledOrder($event);
            }
        }
    }
}
