<table class="order-table">
    <thead>
    <tr>
        <th> </th>
        <th> Item </th>
        <th> Price </th>
        <th> Quantity </th>
    </tr>
    </thead>
    <tbody>
    @foreach($deliveryOrder->items as $idx=>$doItem)
        @include('emails.delivery_order.line_items.product', ['key' => $idx, 'doItem' => $doItem, 'lineItem' => $doItem->lineItem, 'showPrice' => TRUE])
    @endforeach
    </tbody>
</table>

@if(!empty($deliveryOrder->notes))
    <p class="text">
        <strong>Notes:</strong><br/>
        {!! nl2br($deliveryOrder->notes) !!}
    </p>
@endif