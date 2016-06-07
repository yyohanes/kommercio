<?php

namespace Kommercio\Events;

use Kommercio\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Kommercio\Models\Order\Order;

class OrderUpdate extends Event
{
    use SerializesModels;

    public $order;
    public $originalStatus;
    public $notify_customer = true;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Order $order, $originalStatus, $notify_customer = TRUE)
    {
        $this->order = $order;
        $this->originalStatus = $originalStatus;
        $this->notify_customer = $notify_customer;
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
