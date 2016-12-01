<tr class="line-item">
    <td>
        <div>{!! $lineItem->product->getThumbnail()?'<img style="width: 80px; height: auto;" class="product-image" src="'.asset($lineItem->product->getThumbnail()->getImagePath('backend_thumbnail')).'" />':'' !!}
            {{ $lineItem->name }}
        </div>
        @if(!empty($lineItem->notes))
        <br/>
        <blockquote>
            <small>
                {!! nl2br($lineItem->notes) !!}
            </small>
        </blockquote>
        @endif
    </td>
    <!--
    <td>
        {{ PriceFormatter::formatNumber($lineItem->base_price, $lineItem->order->currency) }}
    </td>
    -->
    <td>
        @if(empty($child))
        {{ PriceFormatter::formatNumber($lineItem->net_price, $lineItem->order->currency) }}
        @endif
    </td>
    <td>
        {{ $lineItem->quantity }}
    </td>
    <td>
        @if(empty($child))
        {{ PriceFormatter::formatNumber($lineItem->calculateSubtotal(), $lineItem->order->currency) }}
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
        @include('backend.order.line_items.view.product', ['composite' => $composite, 'lineItem' => $child, 'child' => true])
    @endforeach
@endforeach