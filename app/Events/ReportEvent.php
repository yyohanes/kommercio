<?php

namespace Kommercio\Events;

use Kommercio\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Kommercio\Models\Order\Order;

class ReportEvent extends Event
{
    use SerializesModels;

    public $type;
    public $params;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($type, $params = [])
    {
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
