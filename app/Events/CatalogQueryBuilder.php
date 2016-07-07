<?php

namespace Kommercio\Events;

use Kommercio\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CatalogQueryBuilder extends Event
{
    use SerializesModels;

    public $type;
    public $queryBuilder;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($type, $qb)
    {
        $this->type = $type;
        $this->queryBuilder = $qb;
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
