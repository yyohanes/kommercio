<?php

$factory->define(\Kommercio\Models\CMS\Menu::class, function (Faker\Generator $faker) {
    $name = $faker->words(2, true);

    return [
        'name' => $name,
        'slug' => str_slug($name),
        'description' => $faker->words(),
    ];
});

$factory->define(\Kommercio\Models\CMS\MenuItem::class, function (Faker\Generator $faker) {
    $name = $faker->words(2, true);

    return [
        'name' => $name,
        'menu_id' => function () {
            return factory(\Kommercio\Models\CMS\Menu::class)->create()->id;
        },
        'active' => true,
        'menu_class' => str_slug($faker->word),
        'url' => $faker->url,
        'data' => serialize([
            'target' => $faker->randomElement(['_self', '_blank']),
        ]),
    ];
});
