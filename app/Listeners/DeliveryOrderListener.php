<?php

namespace Kommercio\Listeners;

use Illuminate\Http\Request;
use Kommercio\Events\DeliveryOrderEvent;
use Kommercio\Events\OrderUpdate;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Kommercio\Facades\OrderHelper;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Order\OrderComment;

class DeliveryOrderListener
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

    public function onShippedDeliveryOrder(DeliveryOrderEvent $event)
    {
        if(isset($event->params['send_notification']) && $event->params['send_notification']){
            OrderHelper::sendDeliveryOrderEmail($event->deliveryOrder, 'shipped');
        }
    }

    /**
     * Handle the event.
     *
     * @param  OrderUpdate  $event
     * @return void
     */
    public function handle(DeliveryOrderEvent $event)
    {
        $deliveryOrder = $event->deliveryOrder;
        $order = $deliveryOrder->order;

        //Init profiles
        $deliveryOrder->load('shippingProfile');
        $deliveryOrder->shippingProfile->fillDetails();

        switch ($event->type) {
            case DeliveryOrderEvent::ON_SHIPPED_DELIVERY_ORDER:
                $this->onShippedDeliveryOrder($event);
                break;
            default:
                break;
        }
    }
}
