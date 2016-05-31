<tr class="line-item" data-line_item="fee" data-line_item_key="{{ $key }}">
    <td colspan="2">
        {{ $lineItem->name }}
    </td>
    <td colspan="2"></td>
    <td>
        {{ PriceFormatter::formatNumber($lineItem->calculateTotal(), $lineItem->order->currency) }}
    </td>
</tr>