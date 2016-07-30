@extends('emails.master.default')

@section('content')
<!-- content -->
<div class="content">
    <table bgcolor="" class="social" width="100%">
        <tr>
            <td align="center">
                <h1>THANK YOU FOR YOUR ORDER #{{ $order->reference }}</h1>

                <p class="text">Dear {{ $order->billingInformation->full_name}},</p>
                <p class="text">
                    Your ORDER # {{ $order->reference }} has been placed on {{ $order->checkout_at->format('d M Y') }}</p>
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
                        <h6>ORDER DETAILS</h6>
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

    @if(ProjectHelper::getConfig('require_billing_information'))
    <div class="column">
        <table bgcolor="" class="social" width="100%">
            <tbody>
            <tr>
                <td>
                    <p class="text">
                        <strong>Billing Information</strong><br/>
                        {{ $order->billingInformation->full_name }}<br/>
                        {{ $order->billingInformation->phone_number }}<br/>
                        {!! AddressHelper::printAddress($order->billingInformation->getDetails()) !!}
                    </p>
                </td>
            </tr>
            </tbody></table>
    </div>
    @endif

    <div class="column">
        <table bgcolor="" class="social" width="100%">
            <tbody>
            <tr>
                <td>
                    <p class="text">
                        <strong>Shipping Information</strong><br/>
                        {{ $order->shippingInformation->full_name }}<br/>
                        {{ $order->shippingInformation->phone_number }}<br/>
                        {!! AddressHelper::printAddress($order->shippingInformation->getDetails()) !!}
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

    @if($order->getShippingLineItem())
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
                            <h6>SHIPPING</h6>
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

    <div class="content">
        <table>
            <tbody><tr>
                <td>
                    <p class="text"><strong>{{ $order->getShippingLineItem()->name }}</strong></p>
                </td>
            </tr>
            </tbody></table>
    </div>
    @endif

    @if($order->paymentMethod)
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
                            <h6>PAYMENT</h6>
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

    <div class="content">
        <table>
            <tbody><tr>
                <td class="text">
                    {!! $order->paymentMethod->message !!}
                </td>
            </tr>
            </tbody></table>
    </div>
    @endif
</div>
@stop