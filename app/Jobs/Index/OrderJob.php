<?php

namespace Kommercio\Jobs\Index;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
            'ip_address' => $order->ip_address,
            'user_agent' => $order->user_agent,
            'data' => $order->data ? unserialize($order->data) : null,
            'delivery_date' => $order->delivery_date ? $order->delivery_date->format('Y-m-d\TH:i:sP') : null,
            'checkout_at' => $order->checkout_at ? $order->checkout_at->format('Y-m-d\TH:i:sP') : null,
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
        ];

        $data['line_items'] = $order->allLineItems->map(function($lineItem) {
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
                'parent_id' => $lineItem->parent_id,
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
        });

        // $indexerConfig = new Config(config('kommercio_indexer.site_id'), config('kommercio_indexer.base_path'));
        $indexerConfig = new Config('irv_sg', 'http://docker.for.mac.localhost:3000');
        $indexerService = new OrderService($indexerConfig);
        $indexerService->indexOrder($data);
    }

    /**
     * @param Profile $profile
     * @return array
     */
    protected function getProfileAddress(Profile $profile) {
        return [
            'country' => $profile->country ? [
                'iso_code' => $profile->country->iso_code,
                'name' => $profile->country->name,
            ] : null,
            'state' => $profile->state ? [
                'name' => $profile->state->name,
            ] : null,
            'city' => $profile->city ? [
                'name' => $profile->city->name,
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
