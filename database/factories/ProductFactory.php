<?php

$factory->define(\Kommercio\Models\Product::class, function (Faker\Generator $faker) {
    $name = $faker->name;
    $shortDescription = $faker->text(80);

    return [
        'name' => $name,
        'sku' => $faker->unique()->randomNumber(8),
        'combination_type' => \Kommercio\Models\Product::COMBINATION_TYPE_SINGLE,
        'description' => $faker->text(),
        'description_short' => $shortDescription,
        'box_content' => $faker->text(50),
        'width' => null,
        'length' => null,
        'depth' => null,
        'weight' => null,
    ];
});

$factory->define(\Kommercio\Models\ProductDetail::class, function (Faker\Generator $faker) {
    return [
        'visibility' => \Kommercio\Models\ProductDetail::VISIBILITY_EVERYWHERE,
        'new' => $faker->boolean(25),
        'available' => true,
        'available_date_from' => null,
        'available_date_to' => null,
        'active' => true,
        'active_date_from' => null,
        'active_date_to' => null,
        'retail_price' => $faker->randomNumber(5),
        'currency' => \Kommercio\Facades\CurrencyHelper::getDefaultCurrency(),
        'taxable' => TRUE,
        'store_id' => function () {
            return factory(\Kommercio\Models\Store::class)->create()->id;
        },
        'product_id' => function () {
            return factory(\Kommercio\Models\Product::class)->create()->id;
        }
    ];
});

