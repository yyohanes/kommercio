<?php

namespace Kommercio\Listeners;

use Kommercio\Events\StoreEvent;

class StoreListener
{
    public $coupon;

    /**
     * Handle the event.
     *
     * @param  StoreEvent  $event
     * @return void
     */
    public function handle(StoreEvent $event)
    {
        $type = $event->type;
        $this->store = $event->store;

        if($type == 'store_change') {
            $this->onStoreChange();
        }
    }

    /**
     * Do something on Store change
     * @return void
     */
    protected function onStoreChange()
    {

    }
}
