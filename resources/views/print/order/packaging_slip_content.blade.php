<table class="no-border">
    <tr>
        <td>
            <h4>
                {{ config('project.client_name', config('kommercio.default_name')) }}
            </h4>
        </td>

        <td class="text-right" style="vertical-align: middle;">PACKAGING SLIP</td>
    </tr>
</table>

<table class="no-border">
    <tr>
        <td style="width: 25%;">
            <p>
                <strong>Billing Information</strong><br/>
                {{ $order->billingProfile->full_name }}<br/>
                {{ $order->billingProfile->phone_number }}<br/>
                {!! AddressHelper::printAddress($order->billingProfile->getDetails()) !!}
            </p>
        </td>

        <td style="width: 50px;"></td>

        <td style="width: 25%;">
            @if($order->getShippingMethod()->class != 'PickUp')
                <p>
                    <strong>Shipping Information</strong><br/>
                    {{ $order->shippingProfile->full_name }}<br/>
                    {{ $order->shippingProfile->phone_number }}<br/>
                    {!! AddressHelper::printAddress($order->shippingProfile->getDetails()) !!}
                </p>
            @endif
        </td>

        <td style="width: 50px;"></td>

        <td style="width: 25%;">
            <p>
                <strong>Order #:</strong> {{ $order->reference }}<br/>
                <strong>Order Date:</strong> {{ $order->checkout_at->format('j M Y') }}<br/>
                @if(config('project.enable_delivery_date'))
                    <strong>Delivery Date:</strong> {{ $order->delivery_date->format('j M Y') }}<br/>
                @endif
                <strong>Shipping Method:</strong> {{ $order->getShippingMethod()?$order->getShippingLineItem()->getSelectedMethod()['name']:'' }}<br/>
            </p>
        </td>
    </tr>
</table>

@include('print.order.order_table', ['order' => $order])