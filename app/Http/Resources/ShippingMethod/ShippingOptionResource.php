<?php

namespace Kommercio\Http\Resources\ShippingMethod;

use Illuminate\Http\Resources\Json\Resource;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Models\ShippingMethod\ShippingMethod;

class ShippingOptionResource extends Resource {

    public function toArray($request) {
        $shippingOption = $this->resource;

        $shippingMethod = ShippingMethod::findOrFail($shippingOption['shipping_method_id']);
        $retailPrice = $shippingOption['price']['amount'] ?? 0;
        $retailPriceWithTax = $shippingOption['price']['amount_with_tax'] ?? 0;
        $currency = CurrencyHelper::getCurrency($shippingOption['price']['currency']);

        return [
            'name' => $shippingOption['name'],
            'description' => $shippingOption['description'],
            'machineName' => $shippingOption['machine_name'],
            'price' => [
                'retailPrice' => $retailPrice,
                'retailPriceWithTax' => $retailPriceWithTax,
                'netPrice' => $retailPrice,
                'netPriceWithTax' => $retailPriceWithTax,
                'currency' => [
                    'symbol' => $currency['symbol'],
                    'iso' => $currency['iso'],
                    'thousandSeparator' => $currency['thousand_separator'],
                    'decimalSeparator' => $currency['decimal_separator'],
                ],
            ],
            'shippingMethod' => new ShippingMethodResource($shippingMethod),
        ];
    }
}
