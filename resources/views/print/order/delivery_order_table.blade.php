<table class="content-table">
    <thead>
    <tr>
        <th style="width: 50px;"> </th>
        <th> Item </th>
        <th> Quantity </th>
    </tr>
    </thead>
    <tbody>
    @if($deliveryOrder->items->count() > 0)
        @foreach($deliveryOrder->items as $idx=>$doItem)
            @include('emails.delivery_order.line_items.product', ['key' => $idx, 'doItem' => $doItem, 'lineItem' => $doItem->lineItem, 'showPrice' => FALSE])
        @endforeach
    @endif
    </tbody>
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