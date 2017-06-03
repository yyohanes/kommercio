<?php

namespace Kommercio\Observers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Order\Order;

class OrderObserver
{
    public function saving(Order $order)
    {
        if(empty($order->public_id)){
            $order->generatePublicId();
        }
    }

    public function saved(Order $order)
    {
        // Index flatten order contents
        if($order->checkout_at && in_array($order->status, array_merge(Order::getUsageCountedStatus(), [Order::STATUS_CANCELLED]))){
            $dirty = $order->getDirty();

            // Gather all checkout dates & delivery dates to update
            $toUpdate = [
                'checkout_at' => [],
                'delivery_date' => []
            ];

            if(isset($dirty['checkout_at'])){
                $toUpdate['checkout_at'][] = Carbon::createFromFormat('Y-m-d H:i:s', $order->getOriginal('checkout_at'))->format('Y-m-d');
            }

            if($order->checkout_at){
                $toUpdate['checkout_at'][] = $order->checkout_at->format('Y-m-d');
            }

            if(isset($dirty['delivery_date'])){
                $toUpdate['delivery_date'][] = Carbon::createFromFormat('Y-m-d H:i:s', $order->getOriginal('delivery_date'))->format('Y-m-d');
            }

            if($order->delivery_date){
                $toUpdate['delivery_date'][] = $order->delivery_date->format('Y-m-d');
            }

            $toUpdateProducts = [];
            foreach($order->originalLineItems as $originalLineItem){
                if($originalLineItem->isProduct){
                    $toUpdateProducts[$originalLineItem->line_item_id] = $originalLineItem->product;
                }
            }

            foreach($order->getProductLineItems() as $productLineItem){
                if(!isset($toUpdateProducts[$productLineItem->line_item_id])){
                    $toUpdateProducts[$productLineItem->line_item_id] = $productLineItem->product;
                }
            }

            foreach ($toUpdateProducts as $toUpdateProduct) {
                // Rebuild relevant product order count cache
                $countOptions = [
                    'product_id' => $toUpdateProduct->id,
                    'store_id' => $order->store_id
                ];

                foreach($toUpdate as $toUpdateKey => $toUpdateList){
                    foreach($toUpdateList as $updatedDate){
                        $options = $countOptions + [$toUpdateKey => $updatedDate];
                        Cache::forget(ProjectHelper::flattenArrayToKey($options));
                        $toUpdateProduct->getOrderCount($options);
                    }
                }
            }
        }
    }

    public function deleted(Order $order)
    {
        if($order->forceDeleting){
            if($order->billingProfile){
                $order->billingProfile->delete();
            }

            if($order->shippingProfile){
                $order->shippingProfile->delete();
            }
        }
    }
}