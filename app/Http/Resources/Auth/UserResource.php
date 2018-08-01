<?php

namespace Kommercio\Http\Resources\Auth;

use Illuminate\Http\Resources\Json\Resource;
use Kommercio\Http\Resources\Customer\CustomerResource;
use Kommercio\Models\User;

class UserResource extends Resource {

    public function toArray($request) {
        /** @var User $user */
        $user = $this->resource;
        $user->getProfile()->fillDetails();

        $data = [
            'id' => $user->id,
            'fullName' => $user->getProfile()->full_name,
            'email' => $user->email,
            'status' => $user->status,
            'customer' => $this->whenLoaded('customer', new CustomerResource($user->customer)),
        ];

        return $data;
    }
}
