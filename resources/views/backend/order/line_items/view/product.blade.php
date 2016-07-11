<tr class="line-item" data-taxable="{{ $lineItem->taxable }}" data-line_item="product" data-line_item_key="{{ $key }}">
    <td>
        {!! $lineItem->product->hasThumbnail()?'<img style="width: 80px; height: auto;" src="'.asset($lineItem->product->thumbnail->getImagePath('backend_thumbnail')).'" />':'' !!}
        {{ $lineItem->name }}
    </td>
    <!--
    <td>
        {{ PriceFormatter::formatNumber($lineItem->base_price, $lineItem->order->currency) }}
    </td>
    -->
    <td>
        {{ PriceFormatter::formatNumber($lineItem->net_price, $lineItem->order->currency) }}
    </td>
    <td>
        {{ $lineItem->quantity }}
    </td>
    <td>
        {{ PriceFormatter::formatNumber($lineItem->calculateSubtotal(), $lineItem->order->currency) }}
    </td>
</tr>