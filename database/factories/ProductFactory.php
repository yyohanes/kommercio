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
        'width' => 20,
        'length' => 50,
        'depth' => 40,
        'weight' => 2000,
    ];
});

$factory->define(\Kommercio\Models\ProductDetail::class, function (Faker\Generator $faker) {
    return [
        'visibility' => \Kommercio\Models\ProductDetail::VISIBILITY_EVERYWHERE,
        'retail_price' => $faker->randomNumber(5),
        'currency' => \Kommercio\Facades\CurrencyHelper::getDefaultCurrency(),
        'taxable' => TRUE,
        'product_id' => function () {
            return factory(\Kommercio\Models\Product::class)->create()->id;
        }
    ];
});

