<?php

namespace Kommercio\Http\Resources\Order;

use Illuminate\Http\Resources\Json\Resource;
use Kommercio\Http\Resources\Store\StoreResource;

class OrderResource extends Resource {
    public function toArray($request) {
        $order = $this->resource;

        return [
            'id' => $order->id,
            'publicId' => $order->public_id,
            'reference' => $order->reference,
            'status' => $order->status,
            'deliveryDate' => $order->delivery_date,
            'checkoutAt' => $order->checkout_at,
            'store' => new StoreResource($order->store),
        ];
    }
}
