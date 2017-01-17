<tr class="line-item" data-line_item="fee" data-line_item_key="{{ $key }}">
    <td colspan="2">
        <div>{{ $lineItem->name }}</div>
        @if(!empty($lineItem->notes))
            <br/>
            <div>
                {!! nl2br($lineItem->notes) !!}
            </div>
        @endif
    </td>
    <td>
        {{ PriceFormatter::formatNumber($lineItem->net_price, $lineItem->order->currency) }}
    </td>
    <td></td>
    <td>
        {{ PriceFormatter::formatNumber($lineItem->calculateSubtotal(), $lineItem->order->currency) }}
    </td>
</tr>