<?php

$factory->define(\Kommercio\Models\CMS\Block::class, function (Faker\Generator $faker) {
    $name = $faker->words(2, true);

    return [
        'machine_name' => str_slug($name),
        'name' => $name,
        'body' => $faker->randomHtml(),
        'type' => \Kommercio\Models\CMS\Block::TYPE_STATIC,
        'active' => true,
    ];
});
