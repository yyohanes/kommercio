<?php

$factory->define(\Kommercio\Models\CMS\BannerGroup::class, function (Faker\Generator $faker) {
    $name = $faker->words(2, true);

    return [
        'name' => $name,
        'slug' => str_slug($name),
        'description' => $faker->words(2, true),
    ];
});

$factory->define(\Kommercio\Models\CMS\Banner::class, function (Faker\Generator $faker) {
    $name = $faker->words(2, true);

    return [
        'name' => $name,
        'body' => null,
        'active' => true,
        'banner_group_id' => function () {
            return factory(\Kommercio\Models\CMS\BannerGroup::class)->create()->id;
        },
    ];
});
