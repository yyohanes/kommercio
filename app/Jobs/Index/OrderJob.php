<?php

namespace Kommercio\Jobs\Index;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use KommercioIndexer\Config;
use KommercioIndexer\Services\OrderService;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Profile\Profile;

class OrderJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var Order $order */
    protected $order;

    public function __construct(Order $order) {
        $this->order = $order;
    }

    public function handle() {
        $order = $this->order;
        $customerDetails = $order->customer->getProfile()->fillDetails()->getDetails();
        $billingDetails = $order->billingInformation->fillDetails()->getDetails();
        $shippingDetails = $order->shippingInformation->fillDetails()->getDetails();

        $data = [
            'id' => $order->id,
            'reference' => $order->reference,
            'public_id' => $order->public_id,
            'status' => $order->status,
            'notes' => $order->notes,
            'currency' => $order->currency,
            // Uncomment below to explicitly define currency rate
            // 'currency_rate' => $order->currency_rate,
            'ip_address' => $order->ip_address,
            'user_agent' => $order->user_agent,
            'data' => $order->data ? unserialize($order->data) : null,
            'delivery_date' => $order->delivery_date ? $order->delivery_date->format('Y-m-d\TH:i:sP') : null,
            'checkout_at' => $order->checkout_at ? $order->checkout_at->format('Y-m-d\TH:i:sP') : null,
            'subtotal' => $order->subtotal,
            'additional_total' => $order->additional_total,
            'shipping_total' => $order->shipping_total,
            'discount_total' => $order->discount_total,
            'tax_error_total' => $order->tax_error_total,
            'tax_total' => $order->tax_total,
            'total' => $order->total,
            'total_quantity' => $order->calculateQuantityTotal(),
            'billing_profile' => array_merge(
                [
                    'email' => $billingDetails['email'] ?? null,
                    'phone_number' => $billingDetails['phone_number'] ?? null,
                    'first_name' => $billingDetails['first_name'] ?? null,
                    'last_name' => $billingDetails['last_name'] ?? null,
                ],
                $this->getProfileAddress($order->billingInformation)
            ),
            'shipping_profile' => array_merge(
                [
                    'email' => $shippingDetails['email'] ?? null,
                    'phone_number' => $shippingDetails['phone_number'] ?? null,
                    'first_name' => $shippingDetails['first_name'] ?? null,
                    'last_name' => $shippingDetails['last_name'] ?? null,
                ],
                $this->getProfileAddress($order->shippingInformation)
            ),
            'store' => [
                'id' => $order->store->id,
                'name' => $order->store->name,
                'code' => $order->store->code,
                'type' => $order->store->type,
            ],
            'customer' => [
                'id' => $order->customer->id,
                'email' => $customerDetails['email'] ?? null,
                'phone_number' => $customerDetails['phone_number'] ?? null,
                'first_name' => $customerDetails['first_name'] ?? null,
                'last_name' => $customerDetails['last_name'] ?? null,
            ],
            'payment_method' => $order->paymentMethod->class,
            'shipping_method' => [
                'group' => $order->getShippingMethod() ? $order->getShippingMethod()->class : null,
                'method' => $order->getSelectedShippingMethod(),
            ],
        ];

        $processLineItem = function($lineItem) {
            $data = [
                'line_item_id' => $lineItem->line_item_id,
                'line_item_type' => $lineItem->line_item_type,
                'name' => $lineItem->name,
                'base_price' => $lineItem->base_price,
                'net_price' => $lineItem->net_price,
                'discount_total' => $lineItem->discount_total,
                'tax_total' => $lineItem->tax_total,
                'tax_rate' => $lineItem->tax_rate,
                'quantity' => $lineItem->quantity,
                'total' => $lineItem->total,
                'taxable' => $lineItem->taxable,
                'sort_order' => $lineItem->sort_order,
                'notes' => $lineItem->notes,
                'data' => !empty($lineItem->data) ? unserialize($lineItem->data) : null,
            ];

            switch ($lineItem->line_item_type) {
                case 'product':
                    $data = array_merge($data, [
                        'sku' => $lineItem->product->sku,
                    ]);
                    break;
                case 'shipping':
                    $data = array_merge($data, [
                        'sku' => $lineItem->getData('shipping_method'),
                    ]);
                    break;
                default:
                    break;
            }

            return $data;
        };
        $data['line_items'] = $order->lineItems->map(function($lineItem) use ($processLineItem) {
            $lineItemData = $processLineItem($lineItem);
            if ($lineItem->children->count() > 0) {
                $lineItemData['children'] = $lineItem->children->map($processLineItem);
            }

            return $lineItemData;
        });

        $indexerConfig = new Config(config('kommercio_indexer.site_id'), config('kommercio_indexer.base_path'));
        $indexerService = new OrderService($indexerConfig);
        $indexerService->indexOrder($data);
    }

    /**
     * @param Profile $profile
     * @return array
     */
    protected function getProfileAddress(Profile $profile) {
        return [
            'address_1' => $profile->address_1,
            'address_2' => $profile->address_2,
            'postal_code' => $profile->postal_code,
            'country' => $profile->country ? [
                'iso_code' => $profile->country->iso_code,
                'name' => $profile->country->name,
            ] : null,
            'state' => $profile->state ? [
                'name' => $profile->state->name,
            ] : null,
            'city' => $profile->custom_city || $profile->city ? [
                'name' => $profile->custom_city ?: ($profile->city ? $profile->city->name : null),
            ] : null,
            'district' => $profile->district ? [
                'name' => $profile->district->name,
            ] : null,
            'area' => $profile->area ? [
                'name' => $profile->area->name,
            ] : null,
        ];
    }
}
