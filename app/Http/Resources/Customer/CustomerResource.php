<?php

namespace Kommercio\Http\Resources\Customer;

use Illuminate\Http\Resources\Json\Resource;
use Kommercio\Models\Customer;

class CustomerResource extends Resource {

    public function toArray($request) {
        /** @var Customer $customer */
        $customer = $this->resource;
        $customer->getProfile()->fillDetails();

        return [
            'id' => $customer->id,
            'email' => $customer->email,
            'salute' => $customer->salute,
            'fullName' => $customer->getProfile()->full_name,
            'address_1' => $customer->getProfile()->address_1,
            'address_2' => $customer->getProfile()->address_2,
            'postal_code' => $customer->getProfile()->postal_code,
            'phoneNumber' => $customer->getProfile()->phone_number,
            'homePhone' => $customer->getProfile()->home_phone,
            'birthday' => $customer->getProfile()->birthday,
        ];
    }
}
