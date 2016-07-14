<table class="order-table">
    <thead>
    <tr>
        <th> </th>
        <th> Item </th>
        <th> Price </th>
        <th> Quantity </th>
        <th> Total </th>
    </tr>
    </thead>
    <tbody>
    <?php $shippingLineItems = []; ?>
    @if($order->lineItems)
        @foreach($order->lineItems as $idx=>$lineItem)
            @if($lineItem['line_item_type'] == 'fee')
                @include('emails.order.line_items.fee', ['key' => $idx, 'lineItem' => $lineItem])
            @elseif($lineItem['line_item_type'] == 'product')
                @include('emails.order.line_items.product', ['key' => $idx, 'lineItem' => $lineItem])
            @endif
        @endforeach
    @endif
    </tbody>
    <tfoot>
    <tr>
        <td colspan="2"></td>
        <td colspan="2" align="right"><strong>Subtotal</strong></td>
        <td>{{ PriceFormatter::formatNumber($order->subtotal + $order->additional_total, $order->currency) }}</td>
    </tr>

    @foreach($order->getCartPriceRuleLineItems() as $cartPriceRuleLineItem)
        <tr>
            <td colspan="2"></td>
            <td colspan="2" align="right">{{ $cartPriceRuleLineItem->getPrintName() }}</td>
            <td>{{ PriceFormatter::formatNumber($cartPriceRuleLineItem->total, $order->currency) }}</td>
        </tr>
    @endforeach

    @foreach($order->getCouponLineItems() as $couponLineItem)
        <tr>
            <td colspan="2"></td>
            <td colspan="2" align="right">{{ $couponLineItem->getPrintName() }}</td>
            <td>{{ PriceFormatter::formatNumber($couponLineItem->total, $order->currency) }}</td>
        </tr>
    @endforeach

    <tr>
        <td colspan="2"></td>
        <td colspan="2" align="right"><strong>Shipping</strong></td>
        <td>{{ PriceFormatter::formatNumber($order->shipping_total, $order->currency) }}</td>
    </tr>

    @foreach($order->getTaxLineItems() as $taxLineItem)
        <tr>
            <td colspan="2"></td>
            <td colspan="2" align="right">{{ $taxLineItem->name }}</td>
            <td>{{ PriceFormatter::formatNumber($taxLineItem->total, $order->currency) }}</td>
        </tr>
    @endforeach

    @if($order->rounding_total > 0 || $order->rounding_total < 0)
        <tr>
            <td colspan="2"></td>
            <td colspan="2" align="right"><strong>Rounding</strong></td>
            <td>{{ PriceFormatter::formatNumber($order->rounding_total, $order->currency) }}</td>
        </tr>
    @endif
    <tr>
        <td colspan="2"></td>
        <td colspan="2" align="right"><strong>Grand Total</strong></td>
        <td>{{ PriceFormatter::formatNumber($order->total, $order->currency) }}</td>
    </tr>
    </tfoot>
</table>

@if(!empty($order->notes))
    <p class="text">
    <strong>Notes:</strong><br/>
    {!! nl2br($order->notes) !!}
    </p>
@endif

@foreach($order->additional_fields as $additionalFieldKey => $additionalField)
    <p class="text">
        <strong>{{ trans(LanguageHelper::getTranslationKey('order.additional_fields.'.$additionalFieldKey)) }}:</strong> {!! nl2br($additionalField) !!}
    </p>
@endforeach