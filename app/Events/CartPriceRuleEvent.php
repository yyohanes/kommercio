<?php

namespace Kommercio\Events;

use Illuminate\Http\Request;
use Kommercio\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Kommercio\Models\Order\Order;
use Kommercio\Models\PriceRule\CartPriceRule;
use Kommercio\Models\Store;

class CartPriceRuleEvent extends Event
{
    use SerializesModels;

    public $cartPriceRule;
    public $type;
    public $data;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($type, CartPriceRule $cartPriceRule, $data = [])
    {
        $this->type = $type;
        $this->data = $data;
        $this->cartPriceRule = $cartPriceRule;
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
