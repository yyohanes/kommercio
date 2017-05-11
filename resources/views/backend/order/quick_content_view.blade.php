<table class="table table-hover table-bordered table-striped quick-content-table">
    <thead>
    <tr>
        <th colspan="2"> Item </th>
        <!--
        <th style="width: 20%;"> Original Price </th>
        -->
        <th style="width: 20%;"> Net Price </th>
        <th style="width: 5%;"> Quantity </th>
        <th style="width: 20%;"> Total </th>
    </tr>
    </thead>
    <tbody>
    @if($lineItems)
        @foreach($lineItems as $idx=>$lineItem)
            @if($lineItem['line_item_type'] == 'fee')
                @include('backend.order.line_items.view.fee', ['key' => $idx, 'lineItem' => $lineItem])
            @elseif($lineItem['line_item_type'] == 'product')
                @include('backend.order.line_items.view.product', ['key' => $idx, 'lineItem' => $lineItem])
            @endif
        @endforeach
    @endif
    </tbody>
</table>

<div class="well" id="order-summary">
    <div class="row static-info align-reverse subtotal">
        <div class="col-md-6 name"> Sub Total: </div>
        <div class="col-md-6 value"> {{ PriceFormatter::formatNumber($order->subtotal + $order->additional_total, $order->currency) }} </div>
    </div>

    <div id="cart-price-rules-wrapper">
        @foreach($order->getCartPriceRuleLineItems() as $cartPriceRuleLineItem)
            <div class="row static-info align-reverse tax">
                <div class="col-md-6 name"> {{ $cartPriceRuleLineItem->getPrintName() }}: </div>
                <div class="col-md-6 value"> {{ PriceFormatter::formatNumber($cartPriceRuleLineItem->total, $order->currency) }} </div>
            </div>
        @endforeach

        @foreach($order->getCouponLineItems() as $couponLineItem)
            <div class="row static-info align-reverse tax">
                <div class="col-md-6 name"> {{ $couponLineItem->getPrintName() }}: </div>
                <div class="col-md-6 value"> {{ PriceFormatter::formatNumber($couponLineItem->total, $order->currency) }} </div>
            </div>
        @endforeach
    </div>
    <div class="row static-info align-reverse shipping">
        <div class="col-md-6 name"> Shipping: </div>
        <div class="col-md-6 value"> {{ PriceFormatter::formatNumber($order->shipping_total, $order->currency) }} </div>
    </div>

    <div id="tax-summary-wrapper">
        @foreach($order->getTaxLineItems() as $taxLineItem)
            <div class="row static-info align-reverse tax">
                <div class="col-md-6 name"> {{ $taxLineItem->name }}: </div>
                <div class="col-md-6 value"> {{ PriceFormatter::formatNumber($taxLineItem->total, $order->currency) }} </div>
            </div>
        @endforeach
    </div>

    @if($order->rounding_total > 0 || $order->rounding_total < 0)
        <div class="row static-info align-reverse rounding">
            <div class="col-md-6 name"> Rounding: </div>
            <div class="col-md-6 value"> {{ PriceFormatter::formatNumber($order->rounding_total, $order->currency) }} </div>
        </div>
    @endif
    <div class="row static-info align-reverse total">
        <div class="col-md-6 name"> Grand Total: </div>
        <div class="col-md-6 value"> {{ PriceFormatter::formatNumber($order->total, $order->currency) }} </div>
    </div>
</div>