<?php

namespace Kommercio\Scout\ElasticSearch\Traits;

use ScoutElastic\Searchable;
use Kommercio\Scout\ElasticSearch\Configurators\LineItemIndexConfigurator;

trait LineItemSearchable {
    use Searchable;

    protected $indexConfigurator = LineItemIndexConfigurator::class;
    protected $searchRules = [];
    protected $mapping = [
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
            'order' => [
                'properties' => [
                    'id' => [
                        'type' => 'integer',
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
                ],
            ],
        ]
    ];

    public function toSearchableArray() {
        $billingProfile = $this->order && $this->order->billingInformation ? $this->order->billingInformation->fillDetails() : null;
        $shippingProfile = $this->order && $this->order->shippingInformation ? $this->order->shippingInformation->fillDetails() : null;

        $array = [
            'id' => $this->line_item_id,
            'sku' => $this->getSKU(),
            'name' => $this->getName(),
            'line_item_type' => $this->line_item_type,
            'line_item_description' => $this->name,
            'base_price' => $this->base_price,
            'quantity' => $this->quantity,
            'net_price' => $this->net_price,
            'discount_total' => $this->discount_total,
            'tax_rate' => $this->tax_rate,
            'tax_total' => $this->tax_total,
            'total' => $this->total,
            'taxable' => $this->taxable,
            'notes' => $this->notes,
            'order' => $this->order ? [
                'id' => $this->order->id,
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
                'store' => $this->order->store ? [
                    'id' => $this->order->store->id,
                    'name' => $this->order->store->name,
                    'code' => $this->order->store->code,
                ] : null,
            ] : null,
        ];

        return $array;
    }

    protected function getSKU() {
        if ($this->isProduct) {
            return $this->product->sku;
        } elseif ($this->isShipping) {
            return $this->getData('shipping_method');
        }

        return '';
    }

    protected function getName() {
        if ($this->isProduct) {
            return $this->product->name;
        } elseif ($this->isShipping) {
            return $this->shippingMethod->name;
        } elseif ($this->isTax) {
            return $this->tax->name;
        } elseif ($this->isCartPriceRule) {
            return $this->cartPriceRule->name;
        } elseif ($this->isCoupon) {
            return $this->coupon->name;
        }

        return '';
    }
}
