<?php

namespace Kommercio\Http\Resources\PaymentMethod;

use Illuminate\Http\Resources\Json\Resource;
use Kommercio\Models\PaymentMethod\PaymentMethod;

class PaymentMethodResource extends Resource {

    public function toArray($request) {
        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $this->resource;

        return [
            'id' => $paymentMethod->id,
            'name' => $paymentMethod->name,
            'class' => $paymentMethod->class,
            'message' => $paymentMethod->message,
            'sortOrder' => $paymentMethod->sort_order,
            'active' => !empty($paymentMethod->active),
            'data' => $paymentMethod->getData(),
        ];
    }
}
