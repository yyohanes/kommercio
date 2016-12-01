<?php

namespace Kommercio\Events;

use Kommercio\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Kommercio\Models\RewardPoint\RewardPointTransaction;

class RewardPointEvent extends Event
{
    use SerializesModels;

    public $rewardPointTransaction;
    public $type;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($type, RewardPointTransaction $rewardPointTransaction)
    {
        $this->type = $type;
        $this->rewardPointTransaction = $rewardPointTransaction;
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
