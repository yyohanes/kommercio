<?php

namespace Tests\Feature\Api\Customer;

use Kommercio\Models\Customer;
use Kommercio\Models\Role\Role;
use Kommercio\Models\User;
use Laravel\Passport\Passport;
use TestCase;

class CustomerHTTPTest extends TestCase {
    protected function getTestCustomerData() {
        return [
            'email' => 'test@email.com',
            'full_name' => 'Test Name',
            'phone_number' => '123456',
            'birthday' => '2003-01-01',
        ];
    }

    public function testCreateCustomer() {
        $testData = $this->getTestCustomerData();
        $url = route('api.customer.create');

        $user = factory(User::class)->create();
        $role = Role::create([
            'name' => 'Unauthorized Role',
        ]);
        $user->roles()->attach($role->id);

        Passport::actingAs($user);

        // Create as unauthorized user
        $response = $this->postJson(
            $url,
            $testData
        );
        $response->assertStatus(403);

        // Create as authorized user
        $user = factory(User::class)->create();
        $role = Role::create([
            'name' => 'Authorized Role',
        ]);
        $role->savePermissions(['create_customer']);
        $user->roles()->attach($role->id);

        Passport::actingAs($user);
        $response = $this->postJson(
            $url,
            $testData
        );
        $response->assertStatus(201);

        $createdCustomer = $response->json('data');
        $this->assertEquals($createdCustomer['email'], $testData['email']);
        $this->assertEquals($createdCustomer['fullName'], $testData['full_name']);
        $this->assertEquals($createdCustomer['phoneNumber'], $testData['phone_number']);

        $birthday = new \DateTime($createdCustomer['birthday']['date']);
        $this->assertEquals($birthday->format('Y-m-d'), $testData['birthday']);
    }

    public function testUpdateCustomer() {
        $testCustomer = factory(Customer::class)->create();
        $testData = $this->getTestCustomerData();
        $url = route('api.customer.update', [
            'id' => $testCustomer->id,
        ]);

        // Edit as unauthorized user
        $user = factory(User::class)->create();
        $role = Role::create([
            'name' => 'Unauthorized Role',
        ]);
        $user->roles()->attach($role->id);

        Passport::actingAs($user);

        $response = $this->putJson(
            $url,
            $testData
        );
        $response->assertStatus(403);
        // End Edit as unauthorized user

        // Edit as authorized user
        $user = factory(User::class)->create();
        $role = Role::create([
            'name' => 'Authorized Role',
        ]);
        $role->savePermissions(['edit_customer']);
        $user->roles()->attach($role->id);

        Passport::actingAs($user);
        $response = $this->putJson(
            $url,
            $testData
        );
        $response->assertStatus(200);

        $updatedCustomer = $response->json('data');
        $this->assertEquals($updatedCustomer['email'], $testData['email']);
        $this->assertEquals($updatedCustomer['fullName'], $testData['full_name']);
        $this->assertEquals($updatedCustomer['phoneNumber'], $testData['phone_number']);

        $birthday = new \DateTime($updatedCustomer['birthday']['date']);
        $this->assertEquals($birthday->format('Y-m-d'), $testData['birthday']);
        // End edit as authorized user
    }
}
