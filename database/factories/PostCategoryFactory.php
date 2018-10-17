<?php

$factory->define(\Kommercio\Models\CMS\PostCategory::class, function (Faker\Generator $faker) {
    $name = $faker->words(2, true);

    return [
        'name' => $name,
        'slug' => str_slug($name),
        'body' => $faker->randomHtml(),
        'parent_id' => null,
    ];
});

$factory->state(\Kommercio\Models\CMS\PostCategory::class, 'has_parent', [
    'parent_id' => function () {
        return factory(\Kommercio\Models\CMS\PostCategory::class)->create()->id;
    },
]);

