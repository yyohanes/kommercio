<?php

namespace Kommercio\Http\Resources\Order;

use Illuminate\Http\Resources\Json\Resource;

class OrderResource extends Resource {
    public function toArray($request) {
        $order = $this->resource;

        // TODO: Explicitly define what to return
        return $order->toArray();
    }
}
