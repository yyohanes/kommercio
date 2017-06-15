<?php

$factory->define(\Kommercio\Models\Store::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'code' => $faker->randomNumber(3),
        'type' => \Kommercio\Models\Store::TYPE_ONLINE,
        'default' => false,
    ];
});

$factory->define(\Kommercio\Models\Store\OpeningTime::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->name,
        'open' => TRUE,
        'date_from' => \Carbon\Carbon::now()->format('Y-m-d'),
        'date_to' => \Carbon\Carbon::tomorrow()->format('Y-m-d'),
        'time_from' => '09:00:00',
        'time_to' => '17:00:00',
        'monday' => TRUE,
        'tuesday' => TRUE,
        'wednesday' => TRUE,
        'thursday' => TRUE,
        'friday' => TRUE,
        'saturday' => TRUE,
        'sunday' => TRUE,
    ];
});

