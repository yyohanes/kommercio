<?php

namespace Kommercio\Http\Controllers\Api\Frontend\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Resources\Auth\UserResource;
use Kommercio\Http\Resources\Customer\CustomerResource;

class AuthController extends Controller {

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request) {
        $user = $request->user('api');

        if (!$user) {
            return new JsonResponse('Unknown user.', 400);
        }

        $user->load('customer');
        $response = new UserResource($user);

        return $response->response();
    }
}
