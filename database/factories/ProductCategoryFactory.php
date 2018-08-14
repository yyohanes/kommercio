<?php

$factory->define(\Kommercio\Models\ProductCategory::class, function (Faker\Generator $faker) {
    $name = $faker->name;

    return [
        'name' => $name,
        'description' => $faker->text(),
        'parent_id' => null,
        'active' => true,
        'slug' => str_slug($name),
    ];
});
