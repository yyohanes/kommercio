<?php

namespace Kommercio\Http\Resources\Address;

use Illuminate\Http\Resources\Json\Resource;

class AddressOptionResource extends Resource {

    public function toArray($request) {
        return [
            'name' => $this->resource['name'],
            'id' => $this->resource['id'],
        ];
    }
}
