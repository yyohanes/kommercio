<tr class="line-item">
    <td colspan="2">
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