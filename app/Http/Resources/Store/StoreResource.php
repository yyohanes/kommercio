<?php

namespace Kommercio\Http\Resources\Store;

use Illuminate\Http\Resources\Json\Resource;
use Kommercio\Http\Resources\Address\AddressResource;
use Kommercio\Models\Address\Country;
use Kommercio\Models\Store;

class StoreResource extends Resource {

    public function toArray($request) {
        /** @var Store $store */
        $store = $this->resource;
        $country = $store->country_id ? Country::findById($store->country_id) : null;

        return [
            'id' => $store->id,
            'name' => $store->name,
            'code' => $store->code,
            'type' => $store->type,
            'country' => $country ? new AddressResource($country) : null,
            'isDefault' => !empty($store->default),
        ];
    }
}
