<tr class="line-item" data-taxable="{{ $lineItem->taxable }}" data-line_item="product" data-line_item_key="{{ $key }}">
    <td>
        {{ $lineItem->name }}
    </td>
    <td>
        {{ PriceFormatter::formatNumber($lineItem->base_price, $lineItem->order->currency) }}
    </td>
    <td>
        {{ PriceFormatter::formatNumber($lineItem->net_price, $lineItem->order->currency) }}
    </td>
    <td>
        {{ $lineItem->quantity }}
    </td>
    <td>
        {{ PriceFormatter::formatNumber($lineItem->calculateTotal(), $lineItem->order->currency) }}
    </td>
</tr>