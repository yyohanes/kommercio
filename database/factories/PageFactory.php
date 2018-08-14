<?php

$factory->define(\Kommercio\Models\CMS\Page::class, function (Faker\Generator $faker) {
    $name = $faker->words(2, true);

    return [
        'name' => $name,
        'slug' => str_slug($name),
        'body' => $faker->randomHtml(),
        'active' => true,
    ];
});

$factory->state(\Kommercio\Models\CMS\Page::class, 'has_parent', [
    'parent_id' => function () {
        return factory(\Kommercio\Models\CMS\Page::class)->create()->id;
    },
]);
