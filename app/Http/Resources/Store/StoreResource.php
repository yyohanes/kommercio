<?php

namespace Kommercio\Http\Resources\Store;

use Illuminate\Http\Resources\Json\Resource;
use Kommercio\Models\Store;

class StoreResource extends Resource {

    public function toArray($request) {
        /** @var Store $store */
        $store = $this->resource;

        return [
            'id' => $store->id,
            'name' => $store->name,
            'code' => $store->code,
            'type' => $store->type,
            'isDefault' => !empty($store->default),
        ];
    }
}
