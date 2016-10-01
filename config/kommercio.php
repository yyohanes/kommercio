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
        'product_thumbnail' => [
            'w' => 240,
            'h' => 360,
            'fit' => 'crop',
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
    'print_format' => 'default',
    'require_billing_information' => false,
    'catalog_options' => [
        'shop_url' => 'shop',
        'limit' => 20,
        'sort_by' => 'order',
        'sort_dir' => 'ASC',
    ],
    'order_options' => [
        'limit' => 20,
        'sort_by' => 'checkout_at',
        'sort_dir' => 'ASC',
    ],
    'checkout_options' => [
        'shipping_method_position' => 'review', //review; before_review; before_shipping_address
    ],
    'mailerlite_api_key' => 'f27a47abc3d9162a2c987c05777322f3',
    'mailerlite_subscriber_groups' => [
        'default' => 4404091,
    ],
    'secret_chamber_key' => '$2y$10$/9NFya/4H/PfkkeSRNuF0Oc9fX2OV4xAHaRIhi3/VxZU/ADBSFlza'
];