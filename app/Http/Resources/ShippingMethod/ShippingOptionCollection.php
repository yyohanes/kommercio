<?php

namespace Kommercio\Http\Resources\ShippingMethod;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ShippingOptionCollection extends ResourceCollection {
    public function toArray($request) {
        return ShippingOptionResource::collection($this->collection);
    }
}
