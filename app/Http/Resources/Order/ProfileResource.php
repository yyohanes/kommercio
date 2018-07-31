<?php

namespace Kommercio\Http\Resources\Order;

use Illuminate\Http\Resources\Json\Resource;
use Kommercio\Http\Resources\Address\AddressResource;

class ProfileResource extends Resource {

    public function toArray($request) {
        /** @var ProfileResource $profile */
        $profile = $this->resource;
        $profile->fillDetails();

        return [
            'id' => $profile->id,
            'fullName' => $profile->full_name,
            'phoneNumber' => $profile->phone_number,
            'email' => $profile->email,
            'address1' => $profile->address_1,
            'address2' => $profile->address_2,
            'customCity' => $profile->custom_city,
            'postalCode' => $profile->postal_code,
            'country' => $this->when($profile->country, new AddressResource($profile->country)),
            'state' => $this->when($profile->state, new AddressResource($profile->state)),
            'city' => $this->when($profile->city, new AddressResource($profile->city)),
            'district' => $this->when($profile->district, new AddressResource($profile->district)),
            'area' => $this->when($profile->area, new AddressResource($profile->area)),
        ];
    }
}

