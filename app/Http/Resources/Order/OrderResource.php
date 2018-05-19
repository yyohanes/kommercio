<?php

namespace Kommercio\Http\Resources\Order;

use Illuminate\Http\Resources\Json\Resource;
use Kommercio\Http\Resources\Store\StoreResource;

class OrderResource extends Resource {
    public function toArray($request) {
        $order = $this->resource;

        // TODO: More props
        return [
            'id' => $order->id,
            'publicId' => $order->public_id,
            'reference' => $order->reference,
            'status' => $order->status,
            'deliveryDate' => $order->delivery_date->toIso8601String(),
            'checkoutAt' => $order->checkout_at->toIso8601String(),
            'store' => new StoreResource($order->store),
        ];
    }
}
