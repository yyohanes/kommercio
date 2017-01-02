<tr class="line-item">
    <td>
        {!! $lineItem->product->getThumbnail()?'<img style="width: 80px; height: auto;" src="'.asset($lineItem->product->getThumbnail()->getImagePath('backend_thumbnail')).'" />':'' !!}
    </td>
    <td>
        <div>{{ $lineItem->name }}</div>
        @if($lineItem->product->manufacturer)
            <div>Brand<span class="colon">:</span> {{ $lineItem->product->manufacturer->name }}</div>
        @endif
        @foreach($lineItem->product->productAttributes as $productAttribute)
            <div>{{ $productAttribute->name }}<span class="colon">:</span> {{ $productAttribute->pivot->productAttributeValue->name }}</div>
        @endforeach
    </td>
    <td>
        @if(isset($child) && !$child)
        {{ PriceFormatter::formatNumber($lineItem->net_price, $lineItem->order->currency) }}
        @endif
    </td>
    <td>
        {{ $lineItem->quantity }}
    </td>
    <td>
        @if(isset($child) && !$child)
        {{ PriceFormatter::formatNumber($lineItem->calculateSubtotalWithTax(), $lineItem->order->currency) }}
        @endif
    </td>
</tr>

@foreach($lineItem->product->composites as $composite)
    <tr class="child-line-item-header">
        <td colspan="100">
            {{ $composite->name }}
        </td>
    </tr>

    @foreach($lineItem->getChildrenByComposite($composite) as $child)
        @include('emails.order.line_items.product', ['composite' => $composite, 'lineItem' => $child, 'child' => true])
    @endforeach
@endforeach