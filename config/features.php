<?php
return [
    'order' => [
        'line_item_notes' => false,
        'order_limit' => false,
        'delivery_order' => false,
        'print' => [
            'packaging_slip' => false
        ]
    ],
    'catalog' => [
        'product_attributes' => true,
        'product_features' => false,
        'composite_product' => false,
        'product_configuration' => false,
        'faceted_navigation' => false,
        'import' => false
    ],
    'cms' => [
        'gallery' => false,
        'post' => false,
        'menu' => true
    ],
    'customer' => [
        'reward_points' => false,
        'customer_group' => false,
        'wishlist' => false
    ]
];