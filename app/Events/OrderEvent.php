<?php

namespace Kommercio\Events;

use Kommercio\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Kommercio\Models\Order\Order;

class OrderEvent extends Event
{
    use SerializesModels;

    public $order;
    public $type;
    public $params;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($type, Order $order = null, $params = [])
    {
        $this->order = $order;
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
