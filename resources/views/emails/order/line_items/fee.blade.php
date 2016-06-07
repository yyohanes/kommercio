<tr class="line-item">
    <td colspan="3">
        {{ $lineItem->name }}
    </td>
    <td></td>
    <td>
        {{ PriceFormatter::formatNumber($lineItem->calculateTotal(), $lineItem->order->currency) }}
    </td>
</tr>