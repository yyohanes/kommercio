<?php

$factory->define(\Kommercio\Models\Customer::class, function (Faker\Generator $faker) {
    return [];
});

$factory->afterCreating(Kommercio\Models\Customer::class, function(\Kommercio\Models\Customer $customer, Faker\Generator $faker) {
    $customer->saveProfile([
        'email' => $customer->email,
        'first_name' => $faker->firstName,
        'last_name' => $faker->lastName,
        'phone_number' => $faker->phoneNumber,
        'birthday' => $faker->dateTimeThisCentury->format('Y-m-d'),
    ]);
});

$factory->afterCreatingState(Kommercio\Models\Customer::class, 'user', function(\Kommercio\Models\Customer $customer, Faker\Generator $faker) {
    $customer->loadProfileFields();
    $user = factory(\Kommercio\Models\User::class)->create([
        'email' => $customer->email,
    ]);
    $customer->user()->associate($user);
    $customer->save();
});

