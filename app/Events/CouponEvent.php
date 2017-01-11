<?php

namespace Kommercio\Events;

use Kommercio\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Kommercio\Models\PriceRule\Coupon;

class CouponEvent extends Event
{
    use SerializesModels;

    public $coupon;
    public $type;
    public $params;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($type, Coupon $coupon = null, $params = [])
    {
        $this->coupon = $coupon;
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
