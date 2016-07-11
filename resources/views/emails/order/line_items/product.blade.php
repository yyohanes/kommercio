<tr class="line-item">
    <td>
        {!! $lineItem->product->hasThumbnail()?'<img style="width: 80px; height: auto;" src="'.asset($lineItem->product->thumbnail->getImagePath('backend_thumbnail')).'" />':'' !!}
    </td>
    <td>
        {{ $lineItem->name }}
    </td>
    <td>
        {{ PriceFormatter::formatNumber($lineItem->calculateNet(), $lineItem->order->currency) }}
    </td>
    <td>
        {{ $lineItem->quantity }}
    </td>
    <td>
        {{ PriceFormatter::formatNumber($lineItem->calculateTotal(), $lineItem->order->currency) }}
    </td>
</tr>