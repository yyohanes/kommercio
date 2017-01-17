<tr class="line-item">
    <td>
        @if(!empty($child) && $child)
        {!! $lineItem->product->getThumbnail()?'<img style="width: 40px; height: auto;" src="'.asset($lineItem->product->getThumbnail()->getImagePath('backend_thumbnail')).'" />':'' !!}
        @else
        {!! $lineItem->product->getThumbnail()?'<img style="width: 80px; height: auto;" src="'.asset($lineItem->product->getThumbnail()->getImagePath('backend_thumbnail')).'" />':'' !!}
        @endif
    </td>
    <td>
        <div>{{ $lineItem->name }}</div>
        @if($lineItem->product->manufacturer)
            <div>Brand<span class="colon">:</span> {{ $lineItem->product->manufacturer->name }}</div>
        @endif
        @foreach($lineItem->product->productAttributes as $productAttribute)
            <div>{{ $productAttribute->name }}<span class="colon">:</span> {{ $productAttribute->pivot->productAttributeValue->name }}</div>
        @endforeach

        @foreach($lineItem->children as $childLineItem)
            <div>
                @foreach($lineItem->productConfigurations as $productConfiguration)
                    <br/>- <em>{{ $productConfiguration->pivot->label }}: {{ $productConfiguration->pivot->value }}</em>
                @endforeach
            </div>
        @endforeach

        @if(!empty($lineItem->notes) || $lineItem->productConfigurations->count() > 0)
            @if(!empty($lineItem->notes))
                <div>
                    <em>Notes</em><br/>
                    {!! nl2br($lineItem->notes) !!}
                </div>
            @endif
        @endif
    </td>
    <td>
        {{ PriceFormatter::formatNumber($lineItem->net_price, $lineItem->order->currency) }}
    </td>
    <td>
        {{ $lineItem->quantity }}
    </td>
    <td>
        {{ PriceFormatter::formatNumber($lineItem->calculateSubtotal(), $lineItem->order->currency) }}
    </td>
</tr>

@foreach($lineItem->children as $childLineItem)
    @if($childLineItem->productComposite)
    <tr class="child-line-item-header">
        <td colspan="100">
            {{ $childLineItem->productComposite->name }}
        </td>
    </tr>

    @foreach($lineItem->getChildrenByComposite($childLineItem->productComposite) as $child)
        @include('emails.order.line_items.product', ['composite' => $childLineItem->productComposite, 'lineItem' => $child, 'child' => true])
    @endforeach
    @endif
@endforeach