<?php

namespace Kommercio\Http\Controllers\Api\Frontend\Auth;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use GuzzleHttp\Exception\BadResponseException;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Requests\Api\Auth\LoginRequest;
use Kommercio\Http\Resources\Auth\AuthTokenResource;
use Kommercio\Http\Resources\Auth\UserResource;
use Kommercio\Http\Resources\Customer\CustomerResource;
use Kommercio\Models\User;

class LoginController extends Controller {

    const REFRESH_TOKEN_NAMESPACE = 'refresh_token';

    private $http;

    private $auth;

    private $cookie;

    private $db;

    private $request;

    public function __construct(Application $app) {
        $this->http = new \GuzzleHttp\Client();
        $this->auth = $app->make('auth');
        $this->cookie = $app->make('cookie');
        $this->db = $app->make('db');
        $this->request = $app->make('request');
    }

    public function login(LoginRequest $request) {
        $email = $request->input('email');
        $password = $request->input('password');

        try {
            $user = User::where('email', $email)->firstOrFail();

            $response = $this->proxy(
                'password',
                [
                    'username' => $email,
                    'password' => $password,
                ]
            );
        } catch (ModelNotFoundException $e) {
            throw new \Exception('Credentials don\'t match.', 403);
        } catch (\Throwable $e) {
            throw $e;
        }

        $this->setRefreshTokenCookie($response['refresh_token']);

        $jsonResponse = new AuthTokenResource($response);

        $additional = $this->getAdditionalData($user);
        $jsonResponse->additional([
            'data' => $additional,
        ]);

        return $jsonResponse->response();
    }

    public function refresh(Request $request) {
        $refreshToken = $this->request->cookie(self::REFRESH_TOKEN_NAMESPACE);

        try {
            $response = $this->proxy(
                'refresh_token',
                [
                    'refresh_token' => $refreshToken,
                ]
            );
        } catch (\Throwable $e) {
            throw $e;
        }

        $this->setRefreshTokenCookie($response['refresh_token']);

        $jsonResponse = new AuthTokenResource($response);

        return $jsonResponse->response();
    }

    public function logout() {
        $accessToken = $this->auth->user()->token();

        $this->db
            ->table('oauth_refresh_tokens')
            ->where('access_token_id', $accessToken->id)
            ->update([
                'revoked' => true,
            ]);

        $accessToken->revoke();

        $this->cookie->queue(
            $this->cookie->forget(self::REFRESH_TOKEN_NAMESPACE)
        );
    }

    /**
     * Proxy a request to the OAuth server.
     *
     * @param string $grantType what type of grant type should be proxied
     * @param array $data the data to send to the server
     * @return array
     * @throws BadResponseException
     */
    protected function proxy($grantType, array $data = [])
    {
        $data = array_merge(
            $data,
            [
                'client_id' => config('auth.auth_server.password_client_id'),
                'client_secret' => config('auth.auth_server.password_client_secret'),
                'grant_type' => $grantType,
            ]
        );

        try {
            $guzzleResponse = $this->http->post(
                url(config('auth.auth_server.host') . '/oauth/token'),
                [
                    'form_params' => $data,
                ]
            );
        } catch (BadResponseException $e) {
            throw $e;
        }

        $response = json_decode($guzzleResponse->getBody());

        return [
            'access_token' => $response->access_token,
            'expires_in' => $response->expires_in,
            'refresh_token' => $response->refresh_token,
        ];
    }

    protected function setRefreshTokenCookie($refreshToken) {
        // Create a refresh token cookie
        $this->cookie->queue(
            self::REFRESH_TOKEN_NAMESPACE,
            $refreshToken,
            864000, // 10 days
            null,
            null,
            false,
            true // HttpOnly
        );
    }

    /**
     * @param User $user
     * @return array
     */
    private function getAdditionalData(User $user): array {
        $userResource = new UserResource($user);

        if ($user->isCustomer) {
            $userResource->additional([
                'customer' => new CustomerResource($user->customer),
            ]);
        }

        $additional = [
            'user' => $userResource,
        ];

        return $additional;
    }
}
