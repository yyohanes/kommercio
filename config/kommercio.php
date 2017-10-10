<?php

return [
    'client_name' => 'Kommercio',
    'client_subtitle' => 'Artisan E-commerce',
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
            'status' => ['processing', 'shipped'],
            'fully_shipped' => FALSE
        ],
        'completed' => [
            'status' => ['shipped', 'processing'],
            'outstanding' => 0,
            'fully_shipped' => TRUE,
        ]
    ],
    'print_format' => 'default',
    'require_billing_information' => false,
    'order_options' => [
        'processed_order_status' => ['pending', 'processing'],
        'reference_format' => 'store_code:order_year:order_month:order_day:counter',
        'reference_counter_length' => 4
    ],
    'invoice_options' => [
        'reference_format' => 'store_code:invoice_year:invoice_month:invoice_day:counter',
        'reference_counter_length' => 4,

        // Possible value: (+/-)30(d/wd)|(current_date/delivery_date)
        'additional_due_date_presets' => [],
        'default_due_date_preset' => 'custom'
    ],
    'delivery_order_options' => [
        'reference_format' => 'store_code:delivery_order_year:delivery_order_month:delivery_order_day:counter',
        'reference_counter_length' => 4,
        'check_shipped_on_new_delivery_order' => TRUE
    ],
    'catalog_options' => [
        'shop_url' => 'shop',
        'limit' => 20,
        'sort_by' => 'order',
        'sort_dir' => 'DESC',
    ],
    'post_options' => [
        'limit' => 20,
    ],
    'order_history_options' => [
        'limit' => 20,
        'sort_by' => 'checkout_at',
        'sort_dir' => 'DESC',
    ],
    'checkout_options' => [
        'shipping_method_position' => 'review', //review; before_review; before_shipping_address
    ],
    'newsletter' => [
        'default' => 'mailerlite',
        'mailerlite' => [
            'api_key' => 'f27a47abc3d9162a2c987c05777322f3',
            'subscriber_groups' => [
                'default' => 4404091,
            ],
        ],
        'sendgrid' => [
            'api_key' => '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',
            'subscriber_groups' => [
                'default' => 851021
            ]
        ]
    ],
    'secret_chamber_key' => '$2y$10$/9NFya/4H/PfkkeSRNuF0Oc9fX2OV4xAHaRIhi3/VxZU/ADBSFlza',
    'test_ips' => [
        '127.0.0.1',
        '::1'
    ],
    'cache_control' => [
        'default' => [
            'public' => true,
            'max-age' => 30,
        ],
        'private' => [
            'private' => true
        ]
    ],
];