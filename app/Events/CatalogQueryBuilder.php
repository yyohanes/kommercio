<?php

namespace Kommercio\Events;

use Illuminate\Http\Request;
use Kommercio\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CatalogQueryBuilder extends Event
{
    use SerializesModels;

    public $type;
    public $queryBuilder;
    public $request;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($type, $qb, Request $request = null)
    {
        $this->type = $type;
        $this->queryBuilder = $qb;
        $this->request = $request;
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
