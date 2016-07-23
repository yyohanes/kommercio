<?php

return [
    'default_name' => 'Kommercio',
    'default_subtitle' => 'Artisan E-commerce',
    'backend_prefix' => 'backoffice',
    'images_path' => 'images',
    'image_styles' => [
        'backend_thumbnail' => [
            'w' => 250,
            'h' => 250,
            'fit' => 'crop',
        ],
        'enlarge' => [
            'w' => 800,
            'h' => 800,
        ],
        'small_logo' => [
            'w' => 200,
            'h' => 200
        ],
        'original' => [

        ]
    ],
    'contacts' => [
        'order' => [
            'name' => 'Kommercio',
            'email' => 'order@kommercio.id'
        ],
        'administrator' => [
            'name' => 'Kommercio',
            'email' => 'admin@kommercio.id'
        ],
        'general' => [
            'name' => 'Kommercio',
            'email' => 'admin@kommercio.id'
        ],
    ],
    'home_uri' => 'backoffice',
    'login_images' => [
        'backend/assets/images/login/bg1.jpg',
        'backend/assets/images/login/bg2.jpg'
    ],
    'kommercio_api_token' => 'dGgzzOB5vbWOQXAJmvCjky3qmVY7LzpqX29DxWqLb6gmKyqfwJL7J2TveCeQ',
    'catalog' => [
        'limit' => 20,
        'sort_by' => 'order',
        'sort_dir' => 'ASC',
    ],
    'order_process_condition' => [
        'processing' => [
            'status' => ['pending'],
        ],
        'print' => [
            'status' => ['processing', 'shipped', 'completed'],
        ],
        'shipped' => [
            'status' => ['processing'],
            'printed' => TRUE,
        ],
        'completed' => [
            'status' => ['shipped', 'processing'],
            'outstanding' => 0,
        ]
    ],
    'print_format' => 'default'
];