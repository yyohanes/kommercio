<?php

namespace Kommercio\Events;

use Kommercio\Events\Event;
use Illuminate\Queue\SerializesModels;
use Kommercio\Models\PriceRule;

class ProductPriceRuleEvent extends Event
{
    use SerializesModels;

    public $priceRule;
    public $type;
    public $data;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($type, PriceRule $priceRule, $data = [])
    {
        $this->type = $type;
        $this->data = $data;
        $this->priceRule = $priceRule;
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
