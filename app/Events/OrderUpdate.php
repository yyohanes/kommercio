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
    public $overrideInternalMessage = null;
    public $overrideExternalMessage = null;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Order $order, $originalStatus, $notify_customer = TRUE, $overrideInternalMessage = null, $overrideExternalMessage = null)
    {
        $this->order = $order;
        $this->originalStatus = $originalStatus;
        $this->notify_customer = $notify_customer;
        $this->overrideInternalMessage = $overrideInternalMessage;
        $this->overrideExternalMessage = $overrideExternalMessage;
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
