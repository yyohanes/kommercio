<?php

$factory->define(\Kommercio\Models\Order\Order::class, function (Faker\Generator $faker) {
    return [
        'ip_address' => $faker->ipv4,
        'user_agent' => $faker->userAgent,
        'status' => \Kommercio\Models\Order\Order::STATUS_CART,
        'currency' => \Kommercio\Facades\CurrencyHelper::getDefaultCurrency(),
    ];
});
