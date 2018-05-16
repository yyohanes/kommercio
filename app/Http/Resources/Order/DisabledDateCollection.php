<?php

namespace Kommercio\Http\Resources\Order;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DisabledDateCollection extends ResourceCollection {
    public function toArray($request) {
        return [
            'data' => $this->collection,
        ];
    }
}
