<?php

namespace Kommercio\Http\Resources\ShippingMethod;

use Illuminate\Http\Resources\Json\Resource;
use Kommercio\Models\ShippingMethod\ShippingMethod;

class ShippingMethodResource extends Resource {

    public function toArray($request) {
        /** @var ShippingMethod $shippingMethod */
        $shippingMethod = $this->resource;

        return [
            'id' => $shippingMethod->id,
            'name' => $shippingMethod->name,
            'class' => $shippingMethod->class,
            'message' => $shippingMethod->message,
            'sortOrder' => $shippingMethod->sort_order,
            'taxable' => !empty($shippingMethod->taxable),
            'active' => !empty($shippingMethod->active),
            'requireAddress' => !empty($shippingMethod->requireAddress),
            'requirePostalCode' => !empty($shippingMethod->requirePostalCode),
        ];
    }
}
