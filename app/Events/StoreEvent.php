<?php

namespace Kommercio\Events;

use Illuminate\Http\Request;
use Kommercio\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Kommercio\Models\Store;

class StoreEvent extends Event
{
    use SerializesModels;

    public $store;
    public $type;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($type, Store $store)
    {
        $this->type = $type;
        $this->store = $store;
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
