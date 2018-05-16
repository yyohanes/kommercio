<?php

namespace Kommercio\Http\Resources\Auth;

use Illuminate\Http\Resources\Json\Resource;

class AuthTokenResource extends Resource {

    public function toArray($request) {
        return [
            'access_token' => $this->resource['access_token'],
            'expires_in' => $this->resource['expires_in'],
        ];
    }
}
