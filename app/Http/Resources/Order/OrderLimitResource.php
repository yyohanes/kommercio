<?php

namespace Kommercio\Http\Resources\Order;

use Illuminate\Http\Resources\Json\Resource;
use Kommercio\Http\Resources\Store\StoreResource;

class OrderLimitResource extends Resource {
    public function toArray($request) {
        $orderLimit = $this->resource;

        if (!$orderLimit) return [];

        return [
            'id' => $orderLimit->id,
            'dateFrom' => $orderLimit->date_from,
            'dateTo' => $orderLimit->date_to,
            'type' => $orderLimit->type,
            'limitType' => $orderLimit->limit_type,
            'limit' => $orderLimit->limit + 0,
            'active' => !empty($orderLimit->active),
            'backoffice' => !empty($orderLimit->backoffice),
            'store' => new StoreResource($orderLimit->store),
            'sortOrder' => $orderLimit->sort_order,
        ];
    }
}
