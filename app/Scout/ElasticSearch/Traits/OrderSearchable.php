<?php

namespace Kommercio\Scout\ElasticSearch\Traits;

use ScoutElastic\Searchable;
use Kommercio\Scout\ElasticSearch\Configurators\OrderIndexConfigurator;

trait OrderSearchable {
    use Searchable;

    protected $indexConfigurator = OrderIndexConfigurator::class;
    protected $searchRules = [];
    protected $mapping = [
        'properties' => [
            'id' => [
                'type' => 'integer',
            ],
            'public_id' => [
                'type' => 'keyword',
            ],
            'reference' => [
                'type' => 'text',
            ],
            'line_items' => [
                'type' => 'nested',
                'properties' => [
                    'id' => [
                        'type' => 'integer',
                    ],
                    'order_id' => [
                        'type' => 'integer',
                    ],
                    'sku' => [
                        'type' => 'text',
                        'fields' => [
                            'raw' => [
                                'type' => 'keyword',
                            ],
                        ],
                    ],
                    'name' => [
                        'type' => 'text',
                        'fields' => [
                            'raw' => [
                                'type' => 'keyword',
                            ],
                        ],
                    ],
                    'line_item_type' => [
                        'type' => 'keyword',
                    ],
                    'line_item_description' => [
                        'type' => 'text',
                    ],
                    'base_price' => [
                        'type' => 'float',
                    ],
                    'discount_total' => [
                        'type' => 'float',
                    ],
                    'tax_rate' => [
                        'type' => 'float',
                    ],
                    'tax_total' => [
                        'type' => 'float',
                    ],
                    'net_price' => [
                        'type' => 'float',
                    ],
                    'quantity' => [
                        'type' => 'float',
                    ],
                    'total' => [
                        'type' => 'float',
                    ],
                    'taxable' => [
                        'type' => 'boolean',
                    ],
                    'notes' => [
                        'type' => 'text',
                    ],
                ],
            ],
            'checkout_at' => [
                'type' => 'date',
                'format' => 'date_time_no_millis',
            ],
            'delivery_date' => [
                'type' => 'date',
                'format' => 'date_time_no_millis',
            ],
            'ip_address' => [
                'type' => 'ip',
            ],
            'user_agent' => [
                'type' => 'text',
            ],
            'subtotal' => [
                'type' => 'float',
            ],
            'shipping_total' => [
                'type' => 'float',
            ],
            'discount_total' => [
                'type' => 'float',
            ],
            'tax_total' => [
                'type' => 'float',
            ],
            'rounding_total' => [
                'type' => 'float',
            ],
            'total' => [
                'type' => 'float',
            ],
            'currency' => [
                'type' => 'keyword',
            ],
            'status' => [
                'type' => 'keyword',
            ],
            'notes' => [
                'type' => 'text',
            ],
            'billingInformation' => [
                'properties' => [
                    'first_name' => [
                        'type' => 'text',
                    ],
                    'last_name' => [
                        'type' => 'text',
                    ],
                    'email' => [
                        'type' => 'text',
                        'fields' => [
                            'raw' => [
                                'type' => 'keyword',
                            ],
                        ],
                    ],
                    'phone_number' => [
                        'type' => 'text',
                    ],
                    'country' => [
                        'type' => 'text',
                        'fields' => [
                            'raw' => [
                                'type' => 'keyword',
                            ],
                        ],
                    ],
                    'country_iso_code' => [
                        'type' => 'keyword',
                    ],
                    'state' => [
                        'type' => 'text',
                        'fields' => [
                            'raw' => [
                                'type' => 'keyword',
                            ],
                        ],
                    ],
                    'city' => [
                        'type' => 'text',
                        'fields' => [
                            'raw' => [
                                'type' => 'keyword',
                            ],
                        ],
                    ],
                    'district' => [
                        'type' => 'text',
                        'fields' => [
                            'raw' => [
                                'type' => 'keyword',
                            ],
                        ],
                    ],
                    'area' => [
                        'type' => 'text',
                        'fields' => [
                            'raw' => [
                                'type' => 'keyword',
                            ],
                        ],
                    ],
                    'address_1' => [
                        'type' => 'text',
                    ],
                    'address_2' => [
                        'type' => 'text',
                    ],
                    'postal_code' => [
                        'type' => 'keyword',
                    ],
                ],
            ],
            'shippingInformation' => [
                'properties' => [
                    'first_name' => [
                        'type' => 'text',
                    ],
                    'last_name' => [
                        'type' => 'text',
                    ],
                    'email' => [
                        'type' => 'text',
                        'fields' => [
                            'raw' => [
                                'type' => 'keyword',
                            ],
                        ],
                    ],
                    'phone_number' => [
                        'type' => 'text',
                    ],
                    'country' => [
                        'type' => 'text',
                        'fields' => [
                            'raw' => [
                                'type' => 'keyword',
                            ],
                        ],
                    ],
                    'country_iso_code' => [
                        'type' => 'keyword',
                    ],
                    'state' => [
                        'type' => 'text',
                        'fields' => [
                            'raw' => [
                                'type' => 'keyword',
                            ],
                        ],
                    ],
                    'city' => [
                        'type' => 'text',
                        'fields' => [
                            'raw' => [
                                'type' => 'keyword',
                            ],
                        ],
                    ],
                    'district' => [
                        'type' => 'text',
                        'fields' => [
                            'raw' => [
                                'type' => 'keyword',
                            ],
                        ],
                    ],
                    'area' => [
                        'type' => 'text',
                        'fields' => [
                            'raw' => [
                                'type' => 'keyword',
                            ],
                        ],
                    ],
                    'address_1' => [
                        'type' => 'text',
                    ],
                    'address_2' => [
                        'type' => 'text',
                    ],
                    'postal_code' => [
                        'type' => 'keyword',
                    ],
                ],
            ],
            'customer' => [
                'properties' => [
                    'id' => [
                        'type' => 'integer',
                    ],
                    'first_name' => [
                        'type' => 'text',
                    ],
                    'last_name' => [
                        'type' => 'text',
                    ],
                    'email' => [
                        'type' => 'text',
                        'fields' => [
                            'raw' => [
                                'type' => 'keyword',
                            ],
                        ],
                    ],
                    'phone_number' => [
                        'type' => 'text',
                    ],
                ],
            ],
            'store' => [
                'properties' => [
                    'id' => [
                        'type' => 'integer',
                    ],
                    'name' => [
                        'type' => 'text',
                        'fields' => [
                            'raw' => [
                                'type' => 'keyword',
                            ],
                        ],
                    ],
                    'code' => [
                        'type' => 'keyword',
                    ],
                    'type' => [
                        'type' => 'keyword',
                    ],
                ],
            ],
            '__soft_deleted' => [
                'type' => 'date',
                'format' => 'date_time_no_millis',
            ],
        ]
    ];

    public function toSearchableArray() {
        $customerProfile = $this->customer ? $this->customer->getProfile()->fillDetails() : null;
        $billingProfile = $this->billingInformation ? $this->billingInformation->fillDetails() : null;
        $shippingProfile = $this->shippingInformation ? $this->shippingInformation->fillDetails() : null;
        $array = [
            'id' => $this->id,
            'public_id' => $this->public_id,
            'reference' => $this->reference,
            'line_items' => $this->lineItems->map(function($lineItem) {
                return $lineItem->toSearchableArray();
            }),
            'checkout_at' => $this->checkout_at ? $this->checkout_at->format('Y-m-d\TH:i:sO') : null,
            'delivery_date' => $this->delivery_date ? $this->delivery_date->format('Y-m-d\TH:i:sO') : null,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'subtotal' => $this->subtotal,
            'shipping_total' => $this->shipping_total,
            'discount_total' => $this->discount_total,
            'tax_total' => $this->tax_total,
            'rounding_total' => $this->rounding_total,
            'total' => $this->total,
            'currency' => $this->currency,
            'status' => $this->status,
            'notes' => $this->notes,
            'customer' => $customerProfile ? [
                'id' => $this->customer->id,
                'first_name' => $customerProfile->first_name,
                'last_name' => $customerProfile->last_name,
                'email' => $customerProfile->email,
            ] : null,
            'billingInformation' => $billingProfile ? [
                'first_name' => $billingProfile->first_name,
                'last_name' => $billingProfile->last_name,
                'email' => $billingProfile->email,
                'phone_number' => $billingProfile->email,
                'country_iso_code' => $billingProfile->country ? $billingProfile->country->iso_code : null,
                'country' => $billingProfile->country ? $billingProfile->country->name : null,
                'state' => $billingProfile->state ? $billingProfile->state->name : null,
                'city' => $billingProfile->city ? $billingProfile->city->name : $billingProfile->custom_city,
                'district' => $billingProfile->district ? $billingProfile->district->name : null,
                'area' => $billingProfile->area ? $billingProfile->area->name : null,
                'address_1' => $billingProfile->address_1,
                'address_2' => $billingProfile->address_2,
                'postal_code' => $billingProfile->postal_code,
            ] : null,
            'shippingInformation' => $shippingProfile ? [
                'first_name' => $shippingProfile->first_name,
                'last_name' => $shippingProfile->last_name,
                'email' => $shippingProfile->email,
                'phone_number' => $shippingProfile->email,
                'country_iso_code' => $shippingProfile->country ? $shippingProfile->country->iso_code : null,
                'country' => $shippingProfile->country ? $shippingProfile->country->name : null,
                'state' => $shippingProfile->state ? $shippingProfile->state->name : null,
                'city' => $shippingProfile->city ? $shippingProfile->city->name : $shippingProfile->custom_city,
                'district' => $shippingProfile->district ? $shippingProfile->district->name : null,
                'area' => $shippingProfile->area ? $shippingProfile->area->name : null,
                'address_1' => $shippingProfile->address_1,
                'address_2' => $shippingProfile->address_2,
                'postal_code' => $shippingProfile->postal_code,
            ] : null,
            'store' => $this->store ? [
                'id' => $this->store->id,
                'name' => $this->store->name,
                'code' => $this->store->code,
            ] : null,
            '__soft_deleted' => $this->{$this->getDeletedAtColumn()} ? $this->{$this->getDeletedAtColumn()}->format('Y-m-d\TH:i:sO') : null,
        ];

        return $array;
    }
}
