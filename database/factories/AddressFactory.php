<?php

/**
 * Country
 */

$factory->define(\Kommercio\Models\Address\Country::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->text(15),
        'country_code' => $faker->randomNumber(2, true),
        'iso_code' => $faker->text(2),
        'has_descendant' => false,
        'show_custom_city' => false,
        'active' => true,
    ];
});

/**
 * State
 */

$factory->define(\Kommercio\Models\Address\State::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->text(15),
        'has_descendant' => false,
        'active' => true,
    ];
});

$factory->state(\Kommercio\Models\Address\State::class, 'has_parent', [
    'parent_id' => function () {
        return factory(\Kommercio\Models\Address\Country::class)->create([
            'has_descendant' => true,
        ])->id;
    },
]);

/**
 * City
 */

$factory->define(\Kommercio\Models\Address\City::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->text(15),
        'has_descendant' => false,
        'active' => true,
    ];
});

$factory->state(\Kommercio\Models\Address\City::class, 'has_parent', [
    'parent_id' => function () {
        return factory(\Kommercio\Models\Address\State::class)->create([
            'has_descendant' => true,
        ])->id;
    },
]);

/**
 * District
 */

$factory->define(\Kommercio\Models\Address\District::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->text(15),
        'has_descendant' => false,
        'active' => true,
    ];
});

$factory->state(\Kommercio\Models\Address\District::class, 'has_parent', [
    'parent_id' => function () {
        return factory(\Kommercio\Models\Address\City::class)->create([
            'has_descendant' => true,
        ])->id;
    },
]);

/**
 * Area
 */

$factory->define(\Kommercio\Models\Address\Area::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->text(15),
        'has_descendant' => false,
        'active' => true,
    ];
});

$factory->state(\Kommercio\Models\Address\Area::class, 'has_parent', [
    'parent_id' => function () {
        return factory(\Kommercio\Models\Address\District::class)->create([
            'has_descendant' => true,
        ])->id;
    },
]);
