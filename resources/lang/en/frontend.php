<?php

return [
    'general' => [
        'not_allowed' => 'You are not allowed to do this',
        'token_expired' => 'Your session is expired. Please login agin.',
        'manufacturer' => 'Brand'
    ],
    'catalog' => [
        'manufacturer' => 'Brand',
        'product_category' => 'Category'
    ],
    'product' => [
        'not_active' => 'This item is not accessible.',
        'composite' => [
            'not_belong' => 'Product can\'t be customized.'
        ]
    ],
    'product_category' => [
        'not_active' => 'This item is not accessible.'
    ],
    'order' => [
        'added_to_cart' => '<span class="product-name">:product</span> has been added to cart.',
        'removed_from_cart' => '<span class="product-name">:product</span> has been removed from cart.',
        'updated_cart' => 'Cart has been updated.',
        'coupon_added' => 'Coupon is successfully added.',
        'coupon_removed' => 'Coupon is successfully removed.',
        'cart_clear' => 'Your cart is cleared.'
    ],
    'bookmark' => [
        'not_exist' => 'Bookmark doesn\'t exist',
        'not_logged_in' => 'Please login to add product to :bookmark',
        'added' => ':product is added to :bookmark',
        'removed' => ':product is removed from :bookmark',
    ],
    'checkout' => [
        'empty_order' => 'Your order is empty.',
        'checkout_complete' => 'Your order is successful. Please check your inbox for email confirmation.',
        'order_not_complete' => 'Order is not completed yet.'
    ],
    'login' => [
        'invalid_password' => 'Password is invalid.'
    ],
    'payment_confirmation' => [
        'success_message' => 'Thank you for confirming your Payment. We will check as process your order.'
    ],
    'member' => [
        'profile_update' => [
            'success_message' => 'Your profile is successfully updated.'
        ],
        'account_update' => [
            'success_message' => 'Your account is successfully updated.'
        ],
        'newsletter' => [
            'subscription_success_message' => 'You are successfully subscribed to our Mailing list.'
        ],
        'address' => [
            'create_success_message' => 'Address is successfully saved.',
            'edit_success_message' => 'Address is successfully updated.',
            'delete_success_message' => 'Address is successfully deleted.',
            'create_new_address' => 'Create New Address',
            'set_default_success_message' => ':address is successfully set as default :type'
        ],
        'reward' => [
            'redeem_successful' => 'You have successfully redeemed :reward.'
        ],
    ],
    'seo' => [
        'member' => [
            'login' => [
                'meta_title' => 'Login',
            ],
            'password' => [
                'email' => [
                    'meta_title' => 'Forget Password',
                ],
                'reset' => [
                    'meta_title' => 'Reset Your Password',
                ]
            ],
            'register' => [
                'meta_title' => 'Register',
            ],
            'dashboard' => [
                'meta_title' => 'My Account',
            ],
            'profile' => [
                'meta_title' => 'Update Profile',
            ],
            'account' => [
                'meta_title' => 'Update Account',
            ],
            'reward_points' => [
                'meta_title' => 'Reward Points',
            ],
            'order' => [
                'history' => [
                    'meta_title' => 'Order History',
                ],
                'view' => [
                    'meta_title' => 'Order #:order_reference',
                ],
            ],
            'address_book' => [
                'index' => [
                    'meta_title' => 'Address Book',
                ],
                'create' => [
                    'meta_title' => 'Create Address Book',
                ],
                'edit' => [
                    'meta_title' => 'Edit Address Book',
                ]
            ],
            'bookmark' => [
                'index' => [
                    'meta_title' => 'My Bookmarks'
                ]
            ]
        ],
        'catalog' => [
            'shop' => [
                'meta_title' => 'Shop'
            ],
            'new_arrival' => [
                'meta_title' => 'New Arrival'
            ],
            'search' => [
                'meta_title' => 'Search: :keyword'
            ],
        ],
        'order' => [
            'cart' => [
                'meta_title' => 'Shopping Cart'
            ],
            'checkout' => [
                'meta_title' => 'Checkout'
            ],
            'checkout_complete' => [
                'meta_title' => 'Checkout Complete'
            ],
            'confirm_payment' => [
                'meta_title' => 'Confirm Payment'
            ]
        ]
    ]
];