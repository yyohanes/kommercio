<?php

$factory->define(\Kommercio\Models\CMS\Post::class, function (Faker\Generator $faker) {
    $name = $faker->words(2, true);

    return [
        'name' => $name,
        'slug' => str_slug($name),
        'teaser' => $faker->realText(),
        'body' => $faker->randomHtml(),
        'active' => true,
    ];
});
