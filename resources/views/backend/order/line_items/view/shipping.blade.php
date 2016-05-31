<tr class="line-item" data-line_item="shipping" data-line_item_key="{{ $key }}">
    <td>
        {{ $lineItem->name }}
    </td>
    <td>
        {{ PriceFormatter::formatNumber($lineItem->base_price, $lineItem->order->currency) }}
    </td>
    <td colspan="2"></td>
    <td>
        {{ PriceFormatter::formatNumber($lineItem->calculateTotal(), $lineItem->order->currency) }}
    </td>
</tr>