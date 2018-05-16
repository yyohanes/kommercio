<?php

namespace Kommercio\Http\Resources\Store;

use Illuminate\Http\Resources\Json\ResourceCollection;

class StoreCollection extends ResourceCollection {
    public function toArray($request) {
        return StoreResource::collection($this->collection);
    }
}
