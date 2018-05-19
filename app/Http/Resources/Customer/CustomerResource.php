<?php

namespace Kommercio\Http\Resources\Customer;

use Illuminate\Http\Resources\Json\Resource;
use Carbon\Carbon;
use Kommercio\Models\Customer;

class CustomerResource extends Resource {

    public function toArray($request) {
        /** @var Customer $customer */
        $customer = $this->resource;
        $customer->getProfile()->fillDetails();

        $birthday = $customer->getProfile()->birthday;
        $birthday = $birthday ? Carbon::createFromFormat('Y-m-d', $birthday) : null;

        return [
            'id' => $customer->id,
            'email' => $customer->getProfile()->email,
            'salute' => $customer->getProfile()->salute,
            'fullName' => $customer->getProfile()->full_name,
            'phoneNumber' => $customer->getProfile()->phone_number,
            'homePhone' => $customer->getProfile()->home_phone,
            'birthday' => $birthday,
        ];
    }
}
