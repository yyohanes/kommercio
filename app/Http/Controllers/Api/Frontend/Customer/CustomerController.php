<?php

namespace Kommercio\Http\Controllers\Api\Frontend\Customer;

use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Kommercio\Facades\NewsletterSubscriptionHelper;
use Kommercio\Helpers\UtilityHelper;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Requests\Api\Customer\CustomerFormRequest;
use Kommercio\Http\Resources\Auth\UserResource;
use Kommercio\Http\Resources\Customer\CustomerResource;
use Kommercio\Models\Customer;
use Kommercio\Models\User;

class CustomerController extends Controller {

    public function create(CustomerFormRequest $request) {
        try {
            $customer = $this->createCustomer($request);
        } catch (\Throwable $e) {
            $errors = [
                $e->getMessage(),
            ];

            return new JsonResponse([
                'errors' => $errors,
            ], 422);
        }

        $response = new CustomerResource($customer);

        if ($customer->user) {
            $user = $customer->user;

            $response->additional([
                'user' => new UserResource($user),
            ]);

            event(new Registered($user));
        }

        return $response->response();
    }

    public function update(CustomerFormRequest $request, int $id) {
        $customer = Customer::findById($id);

        $customer = $this->updateCustomer($customer, $request);
        $response = new CustomerResource($customer);

        if ($customer->user) {
            $user = $customer->user;

            $response->additional([
                'data' => [
                    'user' => new UserResource($user),
                ],
            ]);
        }

        return $response->response();
    }

    /**
     * @param CustomerFormRequest $request
     * @return Customer
     * @throws \Throwable
     */
    protected function createCustomer(CustomerFormRequest $request) {
        $accountData = [];

        if ($request->input('_create_account', false)) {
            $accountData = [
                'email' => $request->input('email'),
                'status' => $request->input('user.status', User::STATUS_ACTIVE),
                'password' => $request->input('user.password'),
            ];
        }

        $profileData = [
            'full_name' => $request->input('full_name', null),
            'email' => $request->input('email', null),
            'phone_number' => $request->input('phone_number', null),
            'home_phone' => $request->input('home_phone', null),
            'salute' => $request->input('salute', null),
            'birthday' => $request->input('birthday', null),
        ];

        try {
            $customer = Customer::saveCustomer(
                null,
                $profileData,
                $accountData,
                true,
                true
            );
        } catch (\Throwable $e) {
            throw $e;
        }

        try {
            if (!empty($data['signup_newsletter'])) {
                NewsletterSubscriptionHelper::subscribe(
                    'default',
                    $accountData['email'],
                    $profileData['full_name']
                );
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return $customer;
    }

    /**
     * @param Customer $customer
     * @param CustomerFormRequest $request
     * @return Customer
     */
    protected function updateCustomer(Customer $customer, CustomerFormRequest $request) {
        $profileData = [
            'full_name' => $request->input('full_name', null),
            'email' => $request->input('email', null),
            'phone_number' => $request->input('phone_number', null),
            'home_phone' => $request->input('home_phone', null),
            'salute' => $request->input('salute', null),
            'birthday' => $request->input('birthday', null),
        ];

        $profileData = UtilityHelper::arrayIgnoreNull($profileData);
        if (!empty($profileData)) {
            $customer->saveProfile($profileData);
            $customer->save();
        }

        $accountData = [
            'email' => $request->input('email', null),
            'status' => $request->input('user.status', null),
            'password' => !empty($request->input('user.password'))
                ? bcrypt($request->input('user.password'))
                : null,
        ];

        $accountData = UtilityHelper::arrayIgnoreNull($accountData);
        if (!empty($accountData)) {
            $customer->user->fill($accountData);
            $customer->user->password = $accountData['password'];
            $customer->user->save();
        }

        return $customer;
    }
}
