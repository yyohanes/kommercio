<?php

namespace Kommercio\Listeners;

use Kommercio\Events\OrderEvent;
use Kommercio\Models\Order\Order;

class OrderListener
{
    protected $order;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Handle the event.
     *
     * @param  OrderUpdate  $event
     * @return void
     */
    public function handle(OrderEvent $event)
    {
        $order = $event->order;

        if($event->type == 'before_place_order'){
            $this->beforePlaceOrder($order);
        }
    }

    protected function beforePlaceOrder(Order $order)
    {
        //Call all shipping processing methods
        foreach($order->getShippingLineItems() as $shippingLineItem){
            $shippingLineItem->shippingMethod->getProcessor()->beforePlaceOrder($order);
        }
    }
}
