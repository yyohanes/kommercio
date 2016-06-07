@extends('emails.master.default')

@section('content')
<!-- content -->
<div class="content">
    <table bgcolor="" class="social" width="100%">
        <tr>
            <td>
                <h1>WE ARE PROCESSING YOUR ORDER #{{ $order->reference }}</h1>

                <p class="text">Dear {{ $order->billingProfile->full_name}},</p>
                <p class="text">
                    Your ORDER # {{ $order->reference }} is being processed.</p>
            </td>
        </tr>
    </table>
</div>
<!-- COLUMN WRAP -->
<div class="column-wrap">
    <div class="content">
        <!-- Line -->
        <table width="18" height="81">
            <td>
                <table border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td width="1150" style="border-bottom: 1px solid #e5e5e5;">
                        </td>
                    </tr>
                    <tr>
                        <td>
                        </td>
                    </tr>
                </table>
            </td>
            <!-- DIVIDER TITLE -->
            <td align="center" valign="middle">
                <tr>
                    <td height="0" border="5px" cellspacing="0" cellpadding="0">
                        <h7>ORDER DETAILS</h6>
                    </td>
                </tr>
            </td>
            <td>
                <table border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td width="1150" style="border-bottom: 1px solid #e5e5e5;">
                        </td>
                    </tr>
                    <tr>
                        <td>
                        </td>
                    </tr>
                </table>
            </td>
        </table>
    </div>

    <div class="column">
        <table bgcolor="" class="social" width="100%">
            <tbody>
            <tr>
                <td>
                    <p class="text">
                        <strong>Billing Information</strong><br/>
                        {{ $order->billingProfile->full_name }}<br/>
                        {{ $order->billingProfile->phone_number }}<br/>
                        {!! AddressHelper::printAddress($order->billingProfile->getDetails()) !!}
                    </p>
                </td>
            </tr>
            </tbody></table>
    </div>

    <div class="column">
        <table bgcolor="" class="social" width="100%">
            <tbody>
            <tr>
                <td>
                    <p class="text">
                        <strong>Shipping Information</strong><br/>
                        {{ $order->shippingProfile->full_name }}<br/>
                        {{ $order->shippingProfile->phone_number }}<br/>
                        {!! AddressHelper::printAddress($order->shippingProfile->getDetails()) !!}
                    </p>
                </td>
            </tr>
            </tbody></table>
    </div>

    <div class="content">
        <table>
            <tbody><tr>
                <td>
                    @include('emails.order.order_table', ['lineItems' => $order->lineItems])
                </td>
            </tr>
            </tbody></table>
    </div>
</div>
@stop