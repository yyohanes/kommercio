<tr class="line-item" data-line_item="fee" data-line_item_key="{{ $key }}">
    <td>
        {{ $lineItem->name }}
    </td>
    <td>
        {{ PriceFormatter::formatNumber($lineItem->net_price, $lineItem->order->currency) }}
    </td>
    <td></td>
    <td>
        {{ PriceFormatter::formatNumber($lineItem->calculateSubtotal(), $lineItem->order->currency) }}
    </td>
</tr>