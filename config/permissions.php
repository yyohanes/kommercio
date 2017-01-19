<?php

$permissions = [
    'User' => [
        'permissions' => [
            'view_user' => 'View User',
            'create_user' => 'Create User',
            'edit_user' => 'Edit User',
            'delete_user' => 'Delete User',
        ],
    ],
    'Role' => [
        'permissions' => [
            'view_role' => 'View Role',
            'create_role' => 'Create Role',
            'edit_role' => 'Edit Role',
            'delete_role' => 'Delete Role',
        ]
    ],
    'Product Category' => [
        'permissions' => [
            'view_product_category' => 'View Category',
            'create_product_category' => 'Create Category',
            'edit_product_category' => 'Edit Category',
            'delete_product_category' => 'Delete Category',
        ]
    ],
    'Product' => [
        'permissions' => [
            'view_product' => 'View Product',
            'create_product' => 'Create Product',
            'edit_product' => 'Edit Product',
            'delete_product' => 'Delete Product',
        ]
    ],
    'Product Feature' => [
        'permissions' => [
            'view_product_feature' => 'View Product Feature',
            'create_product_feature' => 'Create Product Feature',
            'edit_product_feature' => 'Edit Product Feature',
            'delete_product_feature' => 'Delete Product Feature',
        ],
        'feature' => 'catalog.product_features'
    ],
    'Product Attribute' => [
        'permissions' => [
            'view_product_attribute' => 'View Product Attribute',
            'create_product_attribute' => 'Create Product Attribute',
            'edit_product_attribute' => 'Edit Product Attribute',
            'delete_product_attribute' => 'Delete Product Attribute',
        ],
        'feature' => 'catalog.product_attributes'
    ],
    'Manufacturer' => [
        'permissions' => [
            'view_manufacturer' => 'View Manufacturer',
            'create_manufacturer' => 'Create Manufacturer',
            'edit_manufacturer' => 'Edit Manufacturer',
            'delete_manufacturer' => 'Delete Manufacturer',
        ]
    ],
    'Product Price Rule' => [
        'permissions' => [
            'view_product_price_rule' => 'View Product Price Rule',
            'create_product_price_rule' => 'Create Product Price Rules',
            'edit_product_price_rule' => 'Edit Product Price Rules',
            'delete_product_price_rule' => 'Delete Product Price Rules',
        ]
    ],
    'Cart Price Rule' => [
        'permissions' => [
            'view_cart_price_rule' => 'View Cart Price Rule',
            'create_cart_price_rule' => 'Create Cart Price Rules',
            'edit_cart_price_rule' => 'Edit Cart Price Rules',
            'delete_cart_price_rule' => 'Delete Cart Price Rules',
        ]
    ],
    'Order' => [
        'permissions' => [
            'view_order' => 'View Order',
            'create_order' => 'Create Order',
            'edit_order' => 'Edit Order',
            'delete_order' => 'Delete Order',
            'place_order' => 'Place Order',
            'process_order' => 'Process Order',
            'ship_order' => 'Ship Order',
            'complete_order' => 'Complete Order',
            'cancel_order' => 'Cancel Order',
            'resend_order_email' => 'Resend Order Email',
            'edit_settled_order' => 'Edit Settled Order',
            'add_unavailable_product' => 'Add Unavailable Product',
            'add_inactive_product' => 'Add Inactive Product',
            'view_order_internal_memo' => 'View Internal Memo',
            'create_order_internal_memo' => 'Create Internal Memo',
            'print_invoice' => 'Print Invoice',
            'print_delivery_note' => 'Print Delivery Note'
        ]
    ],
    'Payments' => [
        'permissions' => [
            'view_payment' => 'View Payment',
            'create_payment' => 'Create Payment',
            'void_payment' => 'Void Payment',
            'confirm_payment' => 'Confirm Payment',
        ]
    ],
    'Order Limit' => [
        'permissions' => [
            'view_order_limit' => 'View Order Limit',
            'create_order_limit' => 'Create Order Limit',
            'edit_order_limit' => 'Edit Order Limit',
            'delete_order_limit' => 'Delete Order Limit',
        ],
        'feature' => 'order.order_limit'
    ],
    'Store' => [
        'permissions' => [
            'view_store' => 'View Store',
            'create_store' => 'Create Store',
            'edit_store' => 'Edit Store',
            'delete_store' => 'Delete Store',
        ]
    ],
    'Payment Method' => [
        'permissions' => [
            'view_payment_method' => 'View Payment Method',
            'create_payment_method' => 'Create Payment Method',
            'edit_payment_method' => 'Edit Payment Method',
            'delete_payment_method' => 'Delete Payment Method',
        ]
    ],
    'Shipping Method' => [
        'permissions' => [
            'view_shipping_method' => 'View Shipping Method',
            'create_shipping_method' => 'Create Shipping Method',
            'edit_shipping_method' => 'Edit Shipping Method',
            'delete_shipping_method' => 'Delete Shipping Method',
        ]
    ],
    'Address' => [
        'permissions' => [
            'view_address' => 'View Address',
            'create_address' => 'Create Address',
            'edit_address' => 'Edit Address',
            'delete_address' => 'Delete Address',
        ]
    ],
    'Tax' => [
        'permissions' => [
            'view_tax' => 'View Tax',
            'create_tax' => 'Create Tax',
            'edit_tax' => 'Edit Tax',
            'delete_tax' => 'Delete Tax',
        ]
    ],
    'Warehouse' => [
        'permissions' => [
            'view_warehouse' => 'View Warehouse',
            'create_warehouse' => 'Create Warehouse',
            'edit_warehouse' => 'Edit Warehouse',
            'delete_warehouse' => 'Delete Warehouse',
        ]
    ],
    'Customer' => [
        'permissions' => [
            'view_customer' => 'View Customers',
            'create_customer' => 'Create Customer',
            'edit_customer' => 'Edit Customer',
            'delete_customer' => 'Delete Customer',
        ],
    ],
    'Customer Group' => [
        'permissions' => [
            'view_customer_group' => 'View Customer Group',
            'create_customer_group' => 'Create Customer Group',
            'edit_customer_group' => 'Edit Customer Group',
            'delete_customer_group' => 'Delete Customer Group',
        ],
        'feature' => 'customer.customer_group'
    ],
    'Reward Points' => [
        'permissions' => [
            'view_reward_points' => 'View Reward Points',
            'add_reward_points' => 'Add Reward Points',
            'deduct_reward_points' => 'Deduct Reward Points',
            'skip_approval_reward_points' => 'Skip Reward Points Approval',
            'approve_reward_points' => 'Approve Reward Points',
            'reject_reward_points' => 'Reject Reward Points',
        ],
        'feature' => 'customer.reward_points'
    ],
    'Reward Points Rules' => [
        'permissions' => [
            'view_reward_points_rules' => 'View Reward Points Rules',
            'create_reward_points_rules' => 'Add Reward Points Rule',
            'edit_reward_points_rules' => 'Edit Reward Points Rule',
            'delete_reward_points_rules' => 'Delete Reward Points Rule',
        ],
        'feature' => 'customer.reward_points'
    ],
    'Rewards' => [
        'permissions' => [
            'view_reward' => 'View Reward',
            'create_reward' => 'Add Reward',
            'edit_reward' => 'Edit Reward',
            'delete_reward' => 'Delete Reward',
        ],
        'feature' => 'customer.reward_points'
    ],
    'Redemptions' => [
        'permissions' => [
            'view_redemptions' => 'View Redemptions',
            'create_redemptions' => 'Create Redemptions',
            'mark_used_redemptions' => 'Mark as Used',
        ],
        'feature' => 'customer.reward_points'
    ],
    'Report' => [
        'permissions' => [
            'view_sales_report' => 'View Sales Report',
            'view_delivery_report' => 'View Delivery Report',
        ]
    ],
    'Production Schedule' => [
        'permissions' => [
            'view_production_schedule' => 'View Schedule',
        ]
    ],
    'Pages' => [
        'permissions' => [
            'view_page' => 'View Page',
            'create_page' => 'Create Page',
            'edit_page' => 'Edit Page',
            'delete_page' => 'Delete Page',
        ]
    ],
    'Banners' => [
        'permissions' => [
            'view_banner' => 'View Banner',
            'create_banner' => 'Create Banner',
            'edit_banner' => 'Edit Banner',
            'delete_banner' => 'Delete Banner',
        ]
    ],
    'Banner Groups' => [
        'permissions' => [
            'create_banner_group' => 'Create Banner Group',
            'edit_banner_group' => 'Edit Banner Group',
            'delete_banner_group' => 'Delete Banner Group',
        ]
    ],
    'Post' => [
        'permissions' => [
            'view_post' => 'View Post',
            'create_post' => 'Create Post',
            'edit_post' => 'Edit Post',
            'delete_post' => 'Delete Post',
        ],
        'feature' => 'cms.post'
    ],
    'Post Categories' => [
        'permissions' => [
            'view_post_category' => 'View Post Category',
            'create_post_category' => 'Create Post Category',
            'edit_post_category' => 'Edit Post Category',
            'delete_post_category' => 'Delete Post Category',
        ],
        'feature' => 'cms.post'
    ],
    'Gallery' => [
        'permissions' => [
            'view_gallery' => 'View Gallery',
            'create_gallery' => 'Create Gallery',
            'edit_gallery' => 'Edit Gallery',
            'delete_gallery' => 'Delete Gallery',
        ],
        'feature' => 'cms.gallery'
    ],
    'Gallery Categories' => [
        'permissions' => [
            'view_gallery_category' => 'View Gallery Category',
            'create_gallery_category' => 'Create Gallery Category',
            'edit_gallery_category' => 'Edit Gallery Category',
            'delete_gallery_category' => 'Delete Gallery Category',
        ],
        'feature' => 'cms.gallery'
    ],
    'Menus' => [
        'permissions' => [
            'view_menu' => 'View Menu',
            'create_menu' => 'Create Menu',
            'edit_menu' => 'Edit Menu',
            'delete_menu' => 'Delete Menu',
        ],
        'feature' => 'cms.menu'
    ],
    'Menu Items' => [
        'permissions' => [
            'create_menu_item' => 'Create Menu Item',
            'edit_menu_item' => 'Edit Menu Item',
            'delete_menu_item' => 'Delete Menu Item',
        ],
        'feature' => 'cms.menu'
    ],
    'Import' => [
        'permissions' => [
            'import_product' => 'Import Product',
            'import_product_attribute' => 'Import Product Attribute',
            'import_manufacturer' => 'Import Manufacturer',
        ],
        'feature' => 'catalog.import'
    ],
];

if(file_exists(base_path('packages/project/src/Project/config/permissions.php'))){
    include_once(base_path('packages/project/src/Project/config/permissions.php'));
}

return $permissions;