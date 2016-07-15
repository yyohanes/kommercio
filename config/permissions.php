<?php

$permissions = [
    'User' => [
        'view_user' => 'View User',
        'create_user' => 'Create User',
        'edit_user' => 'Edit User',
        'delete_user' => 'Delete User',
    ],
    'Role' => [
        'view_role' => 'View Role',
        'create_role' => 'Create Role',
        'edit_role' => 'Edit Role',
        'delete_role' => 'Delete Role',
    ],
    'Product Category' => [
        'view_product_category' => 'View Category',
        'create_product_category' => 'Create Category',
        'edit_product_category' => 'Edit Category',
        'delete_product_category' => 'Delete Category',
    ],
    'Product' => [
        'view_product' => 'View Product',
        'create_product' => 'Create Product',
        'edit_product' => 'Edit Product',
        'delete_product' => 'Delete Product',
    ],
    'Product Feature' => [
        'view_product_feature' => 'View Product Feature',
        'create_product_feature' => 'Create Product Feature',
        'edit_product_feature' => 'Edit Product Feature',
        'delete_product_feature' => 'Delete Product Feature',
    ],
    'Product Attribute' => [
        'view_product_attribute' => 'View Product Attribute',
        'create_product_attribute' => 'Create Product Attribute',
        'edit_product_attribute' => 'Edit Product Attribute',
        'delete_product_attribute' => 'Delete Product Attribute',
    ],
    'Manufacturer' => [
        'view_manufacturer' => 'View Manufacturer',
        'create_manufacturer' => 'Create Manufacturer',
        'edit_manufacturer' => 'Edit Manufacturer',
        'delete_manufacturer' => 'Delete Manufacturer',
    ],
    'Product Price Rule' => [
        'view_product_price_rule' => 'View Product Price Rule',
        'create_product_price_rule' => 'Create Product Price Rules',
        'edit_product_price_rule' => 'Edit Product Price Rules',
        'delete_product_price_rule' => 'Delete Product Price Rules',
    ],
    'Cart Price Rule' => [
        'view_cart_price_rule' => 'View Cart Price Rule',
        'create_cart_price_rule' => 'Create Cart Price Rules',
        'edit_cart_price_rule' => 'Edit Cart Price Rules',
        'delete_cart_price_rule' => 'Delete Cart Price Rules',
    ],
    'Order' => [
        'view_order' => 'View Order',
        'create_order' => 'Create Order',
        'edit_order' => 'Edit Order',
        'delete_order' => 'Delete Order',
        'place_order' => 'Place Order',
        'process_order' => 'Process Order',
        'ship_order' => 'Ship Order',
        'complete_order' => 'Complete Order',
        'cancel_order' => 'Cancel Order',
        'view_order_internal_memo' => 'View Internal Memo',
        'create_order_internal_memo' => 'Create Internal Memo',
        'print_invoice' => 'Print Invoice',
        'print_delivery_note' => 'Print Delivery Note'
    ],
    'Payments' => [
        'view_payment' => 'View Payment',
        'create_payment' => 'Create Payment',
        'void_payment' => 'Void Payment',
        'confirm_payment' => 'Confirm Payment',
    ],
    'Order Limit' => [
        'view_order_limit' => 'View Order Limit',
        'create_order_limit' => 'Create Order Limit',
        'edit_order_limit' => 'Edit Order Limit',
        'delete_order_limit' => 'Delete Order Limit',
    ],
    'Store' => [
        'view_store' => 'View Store',
        'create_store' => 'Create Store',
        'edit_store' => 'Edit Store',
        'delete_store' => 'Delete Store',
    ],
    'Payment Method' => [
        'view_payment_method' => 'View Payment Method',
        'create_payment_method' => 'Create Payment Method',
        'edit_payment_method' => 'Edit Payment Method',
        'delete_payment_method' => 'Delete Payment Method',
    ],
    'Shipping Method' => [
        'view_shipping_method' => 'View Shipping Method',
        'create_shipping_method' => 'Create Shipping Method',
        'edit_shipping_method' => 'Edit Shipping Method',
        'delete_shipping_method' => 'Delete Shipping Method',
    ],
    'Address' => [
        'view_address' => 'View Address',
        'create_address' => 'Create Address',
        'edit_address' => 'Edit Address',
        'delete_address' => 'Delete Address',
    ],
    'Tax' => [
        'view_tax' => 'View Tax',
        'create_tax' => 'Create Tax',
        'edit_tax' => 'Edit Tax',
        'delete_tax' => 'Delete Tax',
    ],
    'Warehouse' => [
        'view_warehouse' => 'View Warehouse',
        'create_warehouse' => 'Create Warehouse',
        'edit_warehouse' => 'Edit Warehouse',
        'delete_warehouse' => 'Delete Warehouse',
    ],
    'Customer' => [
        'view_customer' => 'View Customers',
        'create_customer' => 'Create Customer',
        'edit_customer' => 'Edit Customer',
        'delete_customer' => 'Delete Customer',
    ],
    'Report' => [
        'view_sales_report' => 'View Sales Report',
        'view_delivery_report' => 'View Delivery Report',
    ],
    'Production Schedule' => [
        'view_production_schedule' => 'View Schedule',
    ],
    'Pages' => [
        'view_page' => 'View Page',
        'create_page' => 'Create Page',
        'edit_page' => 'Edit Page',
        'delete_page' => 'Delete Page',
    ],
    'Banners' => [
        'view_banner' => 'View Banner',
        'create_banner' => 'Create Banner',
        'edit_banner' => 'Edit Banner',
        'delete_banner' => 'Delete Banner',
    ],
    'Banner Groups' => [
        'create_banner_group' => 'Create Banner Group',
        'edit_banner_group' => 'Edit Banner Group',
        'delete_banner_group' => 'Delete Banner Group',
    ],
    'Menus' => [
        'view_menu' => 'View Menu',
        'create_menu' => 'Create Menu',
        'edit_menu' => 'Edit Menu',
        'delete_menu' => 'Delete Menu',
    ],
    'Menu Items' => [
        'create_menu_item' => 'Create Menu Item',
        'edit_menu_item' => 'Edit Menu Item',
        'delete_menu_item' => 'Delete Menu Item',
    ],
];

if(file_exists(base_path('packages/project/src/Project/config/permissions.php'))){
    include_once(base_path('packages/project/src/Project/config/permissions.php'));
}

return $permissions;