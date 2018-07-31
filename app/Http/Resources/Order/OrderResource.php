<?php

namespace Kommercio\Http\Resources\Order;

use Illuminate\Http\Resources\Json\Resource;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Http\Resources\Customer\CustomerResource;
use Kommercio\Http\Resources\PaymentMethod\PaymentMethodResource;
use Kommercio\Http\Resources\ShippingMethod\ShippingMethodResource;
use Kommercio\Http\Resources\Store\StoreResource;

class OrderResource extends Resource {
    public function toArray($request) {
        $order = $this->resource;
        $currency = CurrencyHelper::getCurrency($order->currency);
        $paymentMethod = $order->paymentMethod;

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
            'billingProfile' => $this->whenLoaded('billingProfile', new ProfileResource($order->billingProfile)),
            'shippingProfile' => $this->whenLoaded('shippingProfile', new ProfileResource($order->shippingProfile)),
            'lineItems' => $this->whenLoaded('lineItems', LineItemResource::collection($order->lineItems)),
            'customer' => $this->whenLoaded('customer', new CustomerResource($order->customer)),
            'shippingLineItem' => $this->when($order->relationLoaded('lineItems'), new LineItemResource($order->getShippingLineItem())),
            'shippingOption' => $this->when($order->relationLoaded('lineItems'), $order->getSelectedShippingMethod()),
            'shippingMethod' => $this->when($order->relationLoaded('lineItems'), new ShippingMethodResource($order->getShippingMethod())),
            'paymentMethod' => $this->when(!!$paymentMethod, new PaymentMethodResource($paymentMethod)),
        ];
    }
}
