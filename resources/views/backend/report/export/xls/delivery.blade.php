<h3>{{ $shippingMethod }} on {{ $date->format('l, j M Y') }} ({{ $dateType }})</h3>
<h4>Status: {{ implode(', ', $filter['status']) }}</h4>
<?php
$store = $filter['store'];

if($store != 'all'){
    $store = \Kommercio\Models\Store::findOrFail($store);
    $store = $store->name;
}
?>
<h4>Store: {{ $store }}</h4>

<table>
    <thead>
    <tr>
        <th></th>
        <th>Order #</th>
        <th>Purchased On</th>
        <th>Status</th>
        <th>Name</th>
        <th>Phone</th>
        <th>Email</th>
        <th>Address</th>
        @if(ProjectHelper::isFeatureEnabled('customer.customer_group'))
            <th>Cust. Group</th>
        @endif
        @foreach($includedProducts as $includedProduct)
            <th>{{ $includedProduct['product']->name }}</th>
        @endforeach
        <th>Outstanding</th>
        <th>Total</th>
        <th>Payment</th>
        <th>Note</th>
        @if($filter['shipping_method'] == 'all')
            <th>Method</th>
        @endif
    </tr>
    </thead>
    <tbody>
    <?php $total = 0; ?>
    @foreach($orders as $idx=>$order)
        <tr>
            <td>{{ $idx+1 }}</td>
            <td>{{ $order->reference }}</td>
            <td>{{ $order->checkout_at?$order->checkout_at->format('d M Y, H:i'):'' }}</td>
            <td>{{ $order->statusLabel }}</td>
            <td>{{ $order->shipping_full_name }}</td>
            <td>{{ $order->shippingInformation->phone_number }}</td>
            <td>{{ $order->shippingInformation->email }}</td>
            <td>{!! AddressHelper::printAddress($order->shippingInformation->getDetails()) !!}</td>
            @foreach($includedProducts as $includedProduct)
                <td>{{ $order->getProductQuantity($includedProduct['product']->id, true) + 0 }}</td>
            @endforeach
            @if(ProjectHelper::isFeatureEnabled('customer.customer_group'))
                <td>{{ $order->customer ? $order->customer->customerGroups->pluck('name')->implode(', ') : '' }}</td>
            @endif
            <td>{{ $order->outstanding }}</td>
            <td>{{ CurrencyHelper::convert($order->total, $order->currency, $order->currency) }}</td>
            <td>{{ $order->paymentMethod->name }}</td>
            <td>
                @if($order->notes)
                    <p>{!! nl2br($order->notes) !!}</p>
                @endif

                @foreach($order->additional_fields as $additionalFieldKey => $additionalField)
                    <div>
                        <strong>{{ trans(LanguageHelper::getTranslationKey('order.additional_fields.'.$additionalFieldKey)) }}:</strong> {!! nl2br($additionalField) !!}
                    </div>
                @endforeach
            </td>
            @if($filter['shipping_method'] == 'all')
                <td>{{ $order->getShippingLineItem()->getSelectedMethod('name') }}</td>
            @endif
        </tr>
        <?php $total += CurrencyHelper::convert($order->total, $order->currency, $order->currency); ?>
    @endforeach
    </tbody>
</table>
