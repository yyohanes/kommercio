<tr class="line-item {{ empty($child)?'':'child-line-item' }}">
    <td>
        {!! $lineItem->product->getThumbnail()?'<img style="width: 80px; height: auto;" class="product-image" src="'.asset($lineItem->product->getThumbnail()->getImagePath('backend_thumbnail')).'" />':'' !!}
    </td>
    <td>
        <div>{{ $lineItem->name }}</div>
        @if(!empty($lineItem->notes) || $lineItem->productConfigurations->count() > 0)
            <br/>
            @if(!empty($lineItem->notes))
            <div>
                <span class="badge badge-success">Notes</span><br/>
                {!! nl2br($lineItem->notes) !!}
            </div>
            @endif

            @foreach($lineItem->productConfigurations as $productConfiguration)
                <div>
                <span class="badge badge-success">{{ $productConfiguration->pivot->label }}</span><br/>
                {!! nl2br($productConfiguration->pivot->value) !!}
                </div>
            @endforeach
        @endif
    </td>
    <!--
    <td>
        {{ PriceFormatter::formatNumber($lineItem->base_price, $lineItem->order->currency) }}
    </td>
    -->
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

@foreach($lineItem->product->composites as $productComposite)
    <tr class="child-line-item child-line-item-header">
        <td colspan="100">
            {{ $productComposite->name }}
        </td>
    </tr>

    @foreach($lineItem->getChildrenByComposite($productComposite) as $child)
        @include('backend.order.line_items.view.product', ['composite' => $productComposite, 'lineItem' => $child, 'child' => true])
    @endforeach
@endforeach