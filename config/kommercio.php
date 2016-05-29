<?php

return [
    'backend_prefix' => 'secret-chamber',
    'images_path' => 'images',
    'image_styles' => [
        'backend_thumbnail' => [
            'w' => 250,
            'h' => 250,
            'fit' => 'crop',
        ],
        'small_logo' => [
            'w' => 200,
            'h' => 200
        ],
    ],
    'enable_delivery_date' => TRUE,
    'order_number_format' => 'store_code:order_year:order_month:counter',
    'order_number_counter_length' => 4
];