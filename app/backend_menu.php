<?php

$menus['dashboard'] = [
    'prepend' => '<i class="icon-home"></i>',
    'active_path' => 'dashboard',
    'name' => 'Dashboard'
];

$menus['catalog'] = [
    'prepend' => '<i class="fa fa-book"></i>',
    'active_path' => 'catalog',
    'name' => 'Catalog',
    'children' => [
        'product' => [
            'active_path' => 'catalog/product/index',
            'name' => 'Product',
            'route' => 'backend.catalog.product.index',
            'permissions' => 'view_product'
        ],
        'product_category' => [
            'active_path' => 'catalog/category',
            'name' => 'Category',
            'route' => 'backend.catalog.category.index',
            'permissions' => 'view_product_category'
        ],
        'product_attribute' => [
            'active_path' => 'catalog/product-attribute',
            'name' => 'Attribute',
            'route' => 'backend.catalog.product_attribute.index',
            'permissions' => 'view_product_attribute',
            'feature' => 'catalog.product_attributes'
        ],
        'product_feature' => [
            'active_path' => 'catalog/product-feature',
            'name' => 'Feature',
            'route' => 'backend.catalog.product_feature.index',
            'permissions' => 'view_product_feature',
            'feature' => 'catalog.product_features'
        ],
        'product_configuration' => [
            'active_path' => 'catalog/product-configuration/group',
            'name' => 'Configuration',
            'route' => 'backend.catalog.product_configuration.group.index',
            'permissions' => 'view_product_configuration_group',
            'feature' => 'catalog.product_configuration'
        ],
        'product_composite' => [
            'active_path' => 'catalog/product-composite/group',
            'name' => 'Composite',
            'route' => 'backend.catalog.product_composite.group.index',
            'permissions' => 'view_product_composite',
            'feature' => 'catalog.composite_product'
        ],
        'manufacturer' => [
            'active_path' => 'catalog/manufacturer',
            'name' => 'Manufacturer',
            'route' => 'backend.catalog.manufacturer.index',
            'permissions' => 'view_manufacturer'
        ]
    ]
];

$menus['sales'] = [
    'prepend' => '<i class="fa fa-smile-o"></i>',
    'active_path' => 'sales',
    'name' => 'Sales',
    'children' => [
        'order' => [
            'active_path' => 'sales/order/',
            'name' => 'Order',
            'route' => 'backend.sales.order.index',
            'permissions' => 'view_order'
        ],
        'order_limit' => [
            'active_path' => 'sales/order-limit',
            'name' => 'Order Limit',
            'permissions' => 'view_order_limit',
            'feature' => 'order.order_limit',
            'children' => [
                'order_limit_'.\Kommercio\Models\Order\OrderLimit::TYPE_PRODUCT => [
                    'name' => 'Product Order Limit',
                    'active_path' => 'sales/order-limit/'.\Kommercio\Models\Order\OrderLimit::TYPE_PRODUCT,
                    'route' => 'backend.order_limit.index',
                    'route_params' => ['type' => \Kommercio\Models\Order\OrderLimit::TYPE_PRODUCT],
                ],
                'order_limit_'.\Kommercio\Models\Order\OrderLimit::TYPE_PRODUCT_CATEGORY => [
                    'name' => 'Category Order Limit',
                    'active_path' => 'sales/order-limit/'.\Kommercio\Models\Order\OrderLimit::TYPE_PRODUCT_CATEGORY,
                    'route' => 'backend.order_limit.index',
                    'route_params' => ['type' => \Kommercio\Models\Order\OrderLimit::TYPE_PRODUCT_CATEGORY],
                ]
            ]
        ],
    ]
];

$menus['price_rule'] = [
    'prepend' => '<i class="fa fa-tags"></i>',
    'active_path' => 'price-rule',
    'name' => 'Price Rule',
    'children' => [
        'cart_price_rule' => [
            'active_path' => 'price-rule/cart/index',
            'name' => 'Cart Price Rule',
            'route' => 'backend.price_rule.cart.index',
            'permissions' => 'view_cart_price_rule'
        ],
        'product_price_rule' => [
            'active_path' => 'price-rule/product/index',
            'name' => 'Product Price Rule',
            'route' => 'backend.price_rule.product.index',
            'permissions' => 'view_product_price_rule'
        ],
    ]
];

$menus['customer'] = [
    'prepend' => '<i class="fa fa-users"></i>',
    'active_path' => 'customer',
    'name' => 'Customer',
    'children' => [
        'customer' => [
            'active_path' => 'customer/index',
            'name' => 'Customer List',
            'route' => 'backend.customer.index',
            'permissions' => 'view_customer'
        ],
        'customer_group' => [
            'active_path' => 'customer/group/index',
            'name' => 'Customer Group',
            'route' => 'backend.customer.group.index',
            'permissions' => 'view_customer_group',
            'feature' => 'customer.customer_group'
        ],
        'redemption' => [
            'active_path' => 'customer/redemptions',
            'name' => 'Redemptions',
            'route' => 'backend.customer.redemption.index',
            'permissions' => 'view_redemptions',
            'feature' => 'customer.reward_points'
        ],
        'reward_point' => [
            'active_path' => 'customer/reward-point',
            'name' => 'Reward Points',
            'route' => 'backend.customer.reward_point.index',
            'permissions' => 'view_reward_points',
            'feature' => 'customer.reward_points'
        ],
        'reward_rule' => [
            'active_path' => 'customer/reward-rule',
            'name' => 'Reward Rules',
            'route' => 'backend.customer.reward_rule.index',
            'permissions' => 'view_reward_points_rules',
            'feature' => 'customer.reward_points'
        ],
        'reward' => [
            'active_path' => 'customer/rewards',
            'name' => 'Rewards',
            'route' => 'backend.customer.reward.index',
            'permissions' => 'view_reward',
            'feature' => 'customer.reward_points'
        ],
    ]
];

$menus['report'] = [
    'prepend' => '<i class="fa fa-bar-chart"></i>',
    'active_path' => 'report',
    'name' => 'Report',
    'children' => [
        'sales_report' => [
            'active_path' => 'report/sales',
            'name' => 'Sales',
            'route' => 'backend.report.sales_year',
            'permissions' => 'view_sales_report'
        ],
    ]
];

if(config('project.enable_delivery_date', false)){
    $menus['report']['children']['delivery'] = [
        'name' => 'Delivery',
        'route' => 'backend.report.delivery',
        'permissions' => 'view_delivery_report',
        'active_path' => 'report/delivery',
    ];

    $menus['report']['children']['production_schedule'] = [
        'name' => 'Production Schedule',
        'route' => 'backend.report.production_schedule',
        'permissions' => 'view_production_schedule',
        'active_path' => 'report/production-schedule',
    ];
}

$menus['configuration'] = [
    'prepend' => '<i class="fa fa-wrench"></i>',
    'active_path' => 'configuration',
    'name' => 'Configuration',
    'children' => [
        'tax' => [
            'active_path' => 'configuration/tax',
            'name' => 'Tax',
            'route' => 'backend.tax.index',
            'permissions' => 'view_tax'
        ],
        'payment_method' => [
            'active_path' => 'configuration/payment-method',
            'name' => 'Payment Method',
            'route' => 'backend.payment_method.index',
            'permissions' => 'view_payment_method'
        ],
        'shipping_method' => [
            'active_path' => 'configuration/shipping-method',
            'name' => 'Shipping Method',
            'route' => 'backend.shipping_method.index',
            'permissions' => 'view_shipping_method'
        ],
        'address' => [
            'active_path' => 'configuration/address/country',
            'name' => 'Address',
            'route' => 'backend.configuration.address.index',
            'route_params' => ['type' => 'country'],
            'permissions' => 'view_manufacturer'
        ],
        'store' => [
            'active_path' => 'configuration/store',
            'name' => 'Store',
            'route' => 'backend.store.index',
            'permissions' => 'view_store'
        ],
        'warehouse' => [
            'active_path' => 'configuration/warehouse',
            'name' => 'Warehouse',
            'route' => 'backend.warehouse.index',
            'permissions' => 'view_warehouse'
        ],
        'import' => [
            'active_path' => 'utility/import',
            'name' => 'Import',
            'children' => [
                'import_product' => [
                    'active_path' => 'utility/import/product',
                    'name' => 'Product',
                    'route' => 'backend.utility.import.product',
                    'permissions' => 'import_product',
                ],
                'import_product_attribute' => [
                    'active_path' => 'utility/import/product-attribute',
                    'name' => 'Product Attribute',
                    'route' => 'backend.utility.import.product_attribute',
                    'permissions' => 'import_product_attribute',
                ],
                'import_manufacturer' => [
                    'active_path' => 'utility/import/manufacturer',
                    'name' => 'Manufacturer',
                    'route' => 'backend.utility.import.manufacturer',
                    'permissions' => 'import_manufacturer',
                ],
            ]
        ],
    ]
];

$menus['cms'] = [
    'prepend' => '<i class="fa fa-book"></i>',
    'active_path' => 'cms',
    'name' => 'CMS',
    'children' => [
        'menu' => [
            'active_path' => 'menu',
            'name' => 'Menu',
            'route' => 'backend.cms.menu.index',
            'permissions' => 'view_menu',
            'feature' => 'cms.menu',
        ],
        'page' => [
            'active_path' => 'cms/page',
            'name' => 'Page',
            'route' => 'backend.cms.page.index',
            'permissions' => 'view_page'
        ],
        'banner' => [
            'active_path' => 'cms/banner',
            'name' => 'Banner',
            'route' => 'backend.cms.banner_group.index',
            'permissions' => 'view_banner'
        ],
        'gallery' => [
            'active_path' => 'cms/gallery',
            'name' => 'Gallery',
            'permissions' => 'view_gallery',
            'feature' => 'cms.gallery',
            'children' => [
                'gallery' => [
                    'active_path' => 'cms/gallery/index',
                    'name' => 'Gallery',
                    'route' => 'backend.cms.gallery.index',
                    'permissions' => 'view_gallery',
                ],
                'gallery_category' => [
                    'active_path' => 'cms/gallery/category/index',
                    'name' => 'Gallery Category',
                    'route' => 'backend.cms.gallery.category.index',
                    'permissions' => 'view_gallery_category'
                ],
            ]
        ],
        'post' => [
            'active_path' => 'cms/post',
            'name' => 'Post',
            'permissions' => 'view_post',
            'feature' => 'cms.post',
            'children' => [
                'post' => [
                    'active_path' => 'cms/post',
                    'name' => 'Post',
                    'route' => 'backend.cms.post.index',
                    'permissions' => 'view_post'
                ],
                'post_category' => [
                    'active_path' => 'cms/post/category',
                    'name' => 'Post Category',
                    'route' => 'backend.cms.post.category.index',
                    'permissions' => 'view_post_category'
                ],
            ]
        ],
        'block' => [
            'active_path' => 'cms/block/index',
            'name' => 'Blocks',
            'route' => 'backend.cms.block.index',
            'permissions' => 'view_block'
        ],
    ]
];

$menus['access'] = [
    'prepend' => '<i class="fa fa-lock"></i>',
    'active_path' => 'user',
    'name' => 'Access',
    'children' => [
        'user' => [
            'active_path' => 'user/index',
            'name' => 'Users',
            'route' => 'backend.user.index',
            'permissions' => 'view_user'
        ],
        'role' => [
            'active_path' => 'user/role/index',
            'name' => 'Roles',
            'route' => 'backend.user.role.index',
            'permissions' => 'view_role'
        ],
    ]
];

if(file_exists(base_path('packages/project/src/Project/config/backend_menu.php'))){
    include_once(base_path('packages/project/src/Project/config/backend_menu.php'));
}

return $menus;