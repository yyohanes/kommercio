<?php

$factory->define(\Kommercio\Models\PaymentMethod\PaymentMethod::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->words(2, true),
        'class' => ucfirst($faker->words(1, true)),
        'message' => $faker->sentence(),
        'active' => true,
    ];
});
