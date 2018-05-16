<?php

namespace Kommercio\Http\Resources\Auth;

use Illuminate\Http\Resources\Json\Resource;
use Kommercio\Models\User;

class UserResource extends Resource {

    public function toArray($request) {
        /** @var User $user */
        $user = $this->resource;
        $user->getProfile()->fillDetails();

        $data = array_merge(
            [
                'id' => $user->id,
                'fullName' => $user->fullName,
                'email' => $user->email,
                'status' => $user->status,
            ],
            $this->additional
        );

        return $data;
    }
}
