<?php

namespace Kommercio\Http\Resources\Order;

use Illuminate\Http\Resources\Json\Resource;
use Kommercio\Http\Resources\Store\StoreResource;

class DayAvailabilityResource extends Resource {
    public function toArray($request) {
        return $this->resource;
    }
}
