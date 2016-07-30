<tr class="line-item">
    <td>
        {!! $lineItem->product->getThumbnail()?'<img style="width: 80px; height: auto;" src="'.asset($lineItem->product->getThumbnail()->getImagePath('backend_thumbnail')).'" />':'' !!}
    </td>
    <td>
        {{ $lineItem->name }}
    </td>
    <td>
        {{ PriceFormatter::formatNumber($lineItem->net_price, $lineItem->order->currency) }}
    </td>
    <td>
        {{ $lineItem->quantity }}
    </td>
    <td>
        {{ PriceFormatter::formatNumber($lineItem->calculateSubtotalWithTax(), $lineItem->order->currency) }}
    </td>
</tr>