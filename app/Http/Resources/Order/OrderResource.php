<?php

namespace Kommercio\Http\Resources\Order;

use Illuminate\Http\Resources\Json\Resource;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Http\Resources\Store\StoreResource;

class OrderResource extends Resource {
    public function toArray($request) {
        $order = $this->resource;
        $currency = CurrencyHelper::getCurrency($order->currency);

        // TODO: More props
        return [
            'id' => $order->id,
            'publicId' => $order->public_id,
            'reference' => $order->reference,
            'status' => $order->status,
            'deliveryDate' => $order->delivery_date->toIso8601String(),
            'checkoutAt' => $order->checkout_at->toIso8601String(),
            'quantity' => $order->calculateQuantityTotal(),
            'total' => [
                'amount' => $order->calculateTotal(),
                'currency' => [
                    'symbol' => $currency['symbol'],
                    'iso' => $currency['iso'],
                    'thousandSeparator' => $currency['thousand_separator'],
                    'decimalSeparator' => $currency['decimal_separator'],
                ],
            ],
            'store' => new StoreResource($order->store),
        ];
    }
}
