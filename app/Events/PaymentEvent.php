<?php

namespace Kommercio\Events;

use Kommercio\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Kommercio\Models\Order\Payment;

class PaymentEvent extends Event
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
    public function __construct($type, Payment $payment = null, $params = [])
    {
        $this->payment = $payment;
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
