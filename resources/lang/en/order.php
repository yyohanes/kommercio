<?php

return [
    'email' => [
        'confirmation' => [
            'subject' => 'Thank you for your order #:reference'
        ],
        'processing' => [
            'subject' => 'We are processing your order #:reference'
        ],
        'shipped' => [
            'subject' => 'Your order #:reference is shipped'
        ],
        'completed' => [
            'subject' => 'Your order #:reference is completed'
        ],
        'cancelled' => [
            'subject' => 'Your order #:reference is cancelled'
        ]
    ],
    'payment' => [
        'unpaid' => 'Unpaid',
        'paid' => 'Paid',
        'partial' => 'Partially Paid'
    ],
    'coupons' => [
        'successfully_added' => 'Coupon ":coupon_code" is successfully added.',
        'not_exist' => 'Coupon ":coupon_code" is invalid.',
        'invalid' => 'Coupon ":coupon_code" is not valid. Please check your order.',
        'max_usage_exceeded' => 'Coupon ":coupon_code" has reached usage limit.',
        'max_usage_per_email_exceeded' => 'You have used coupon ":coupon_code" before.',
    ],
    'address' => [
        'select_country' => 'Country',
        'select_state' => 'State',
        'select_city' => 'City',
        'select_district' => 'District',
        'select_area' => 'Area',
    ],
    'shipping' => [
        'estimated_working_day' => 'Approximately :estimated working day|Approximately :estimated working days',
    ],
    'memos' => [
        'external' => [
            'confirmation' => 'Order is placed.',
            'processing' => 'Order is being processed.',
            'completed' => 'Order is complete.',
            'partially_shipped' => 'Order is partially being delivered.<br/>Tracking Number: :tracking_number. Delivered by: :delivered_by.',
            'fully_shipped' => 'All items in your order are being delivered.<br/>Tracking Number: :tracking_number. Delivered by: :delivered_by.',
            'cancelled' => 'Order is cancelled due to <em>:reason</em>',
            'payment_received' => 'Payment with amount of :amount is received.',
            'payment_voided' => 'Payment with amount of :amount is voided.',
        ]
    ]
];