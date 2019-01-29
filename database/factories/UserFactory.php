<?php

$factory->define(\Kommercio\Models\User::class, function (Faker\Generator $faker) {
    return [
        'email' => $faker->email,
        'password' => bcrypt($faker->password),
        'status' => \Kommercio\Models\User::STATUS_ACTIVE,
    ];
});
