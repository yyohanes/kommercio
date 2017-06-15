<?php

namespace Kommercio\Events;

use Kommercio\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Kommercio\Models\Order\DeliveryOrder\DeliveryOrder;

class DeliveryOrderEvent extends Event
{
    const ON_NEW_DELIVERY_ORDER = 'on_new_delivery_order';
    const ON_SHIPPED_DELIVERY_ORDER = 'on_shipped_delivery_order';
    const ON_CANCELLED_DELIVERY_ORDER = 'on_cancelled_delivery_order';

    use SerializesModels;

    public $deliveryOrder;
    public $type;
    public $params;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($type, DeliveryOrder $deliveryOrder = null, $params = [])
    {
        $this->deliveryOrder = $deliveryOrder;
        $this->type = $type;
        $this->params = $params;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
