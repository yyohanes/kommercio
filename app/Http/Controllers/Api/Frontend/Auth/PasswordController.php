<?php

namespace Kommercio\Http\Controllers\Api\Frontend\Auth;

use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Requests\Api\Auth\ForgetPasswordFormRequest;

class PasswordController extends Controller {
    use ResetsPasswords;

    /**
     * @param ForgetPasswordFormRequest $request
     * @return JsonResponse
     */
    public function forgetPassword(ForgetPasswordFormRequest $request) {
        $response = $this->broker()->sendResetLink($request->only('email'));

        if ($response != Password::RESET_LINK_SENT) {
            $error = 'Something went wrong. Please try again';

            if ($response === Password::INVALID_USER) {
                $error = 'Account with given credentials not found';
            }

            return new JsonResponse(
                [
                    'errors' => [
                        'email' => [$error],
                    ],
                ],
                422
            );
        }

        $response = [
            'messages' => [
                'Link to reset password is mailed'
            ],
        ];

        return new JsonResponse($response);
    }
}
