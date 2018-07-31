<?php

namespace Kommercio\Http\Resources\Order;

use Illuminate\Http\Resources\Json\Resource;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Http\Resources\Product\ProductResource;
use Kommercio\Models\Order\LineItem;

class LineItemResource extends Resource {

    public function toArray($request) {
        /** @var LineItem $lineItem */
        $lineItem = $this->resource;
        $order = $lineItem->order;
        $currency = CurrencyHelper::getCurrency($order->currency);

        return [
            'id' => $lineItem->id,
            'lineItemId' => $lineItem->line_item_id,
            'lineItemType' => $lineItem->line_item_type,
            'product' => $this->when($lineItem->line_item_type === 'product', new ProductResource($lineItem->product)),
            'name' => $lineItem->name,
            'basePrice' => [
                'amount' => $lineItem->base_price,
                'currency' => [
                    'symbol' => $currency['symbol'],
                    'iso' => $currency['iso'],
                    'thousandSeparator' => $currency['thousand_separator'],
                    'decimalSeparator' => $currency['decimal_separator'],
                ],
            ],
            'netPrice' => [
                'amount' => $lineItem->calculateNet(),
                'currency' => [
                    'symbol' => $currency['symbol'],
                    'iso' => $currency['iso'],
                    'thousandSeparator' => $currency['thousand_separator'],
                    'decimalSeparator' => $currency['decimal_separator'],
                ],
            ],
            'discountTotal' => [
                'amount' => $lineItem->discount_total,
                'currency' => [
                    'symbol' => $currency['symbol'],
                    'iso' => $currency['iso'],
                    'thousandSeparator' => $currency['thousand_separator'],
                    'decimalSeparator' => $currency['decimal_separator'],
                ],
            ],
            'taxTotal' => [
                'amount' => $lineItem->tax_total,
                'currency' => [
                    'symbol' => $currency['symbol'],
                    'iso' => $currency['iso'],
                    'thousandSeparator' => $currency['thousand_separator'],
                    'decimalSeparator' => $currency['decimal_separator'],
                ],
            ],
            'total' => [
                'amount' => $lineItem->calculateTotal(),
                'currency' => [
                    'symbol' => $currency['symbol'],
                    'iso' => $currency['iso'],
                    'thousandSeparator' => $currency['thousand_separator'],
                    'decimalSeparator' => $currency['decimal_separator'],
                ],
            ],
            'quantity' => $lineItem->quantity,
            'taxable' => !empty($lineItem->taxable),
            'sortOrder' => $lineItem->sort_order,
        ];
    }
}
