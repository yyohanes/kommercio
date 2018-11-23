<?php

namespace Kommercio\Http\Resources\Address;

use Illuminate\Http\Resources\Json\Resource;

class AddressResource extends Resource {

    public function toArray($request) {
        $address = $this->resource;

        return [
            'id' => $address->id,
            'name' => $address->name,
            'isoCode' => $address->iso_code,
            'countryCode' => $address->country_code,
            'customCity' => $this->when(
                strtolower($address->addressType) === 'country',
                $address->show_custom_city
            ),
            'remoteCity' => $this->when(
                strtolower($address->addressType) === 'country',
                $address->use_remote_city
            ),
            'hasDescendant' => !!$address->has_descendant,
            'active' => !!$address->active,
            'sortOrder' => $address->sort_order,
            'type' => strtolower($address->addressType),
        ];
    }
}
