<div class="row">
    <div class="col-md-6">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-user"></i>
                    <span class="caption-subject">Customer Information</span>
                </div>
            </div>
            <div class="portlet-body">
                <div id="billing-information-wrapper" data-profile_source="{{ route('backend.sales.order.copy_customer_information', ['type' => 'profile']) }}">
                    @include('backend.order.customer_information', ['type' => 'profile'])
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-truck"></i>
                    <span class="caption-subject">Shipping Information</span>
                </div>
                <div class="actions">
                    <a href="javascript:;" id="shipping-copy-btn" class="btn btn-warning btn-sm">Same as Customer</a>
                </div>
            </div>
            <div class="portlet-body" id="shipping-information-wrapper" data-profile_source="{{ route('backend.sales.order.copy_customer_information', ['type' => 'shipping_profile']) }}">
                @include('backend.order.customer_information', ['type' => 'shipping_profile'])
            </div>
        </div>
    </div>

    @if(config('project.enable_delivery_date', FALSE))
        <div class="col-md-6">
            <div class="portlet light bordered" id="delivery-date-panel">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-clock-o"></i>
                        <span class="caption-subject">Delivery Date</span>
                    </div>
                </div>
                <div class="portlet-body">
                    @include('backend.master.form.fields.text', [
                        'name' => 'delivery_date',
                        'label' => 'Delivery Date',
                        'key' => 'delivery_date',
                        'attr' => [
                            'class' => 'form-control',
                            'data-date-format' => 'yyyy-mm-dd',
                            'id' => 'delivery_date',
                            'placeholder' => 'YYYY-MM-DD'
                        ],
                        'defaultValue' => old('delivery_date', $order->delivery_date?$order->delivery_date->format('Y-m-d'):null)
                    ])
                </div>
            </div>
        </div>
    @endif

    <div class="col-md-3">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-money"></i>
                    <span class="caption-subject">Payment Method</span>
                </div>
            </div>
            <div class="portlet-body" id="payment-method-wrapper">
                <div class="form-group">
                    <div class="col-md-12">
                        <div class="radio-list">
                            @foreach($paymentMethodOptions as $value=>$paymentMethodOption)
                                <label>{!! Form::radio('payment_method', $value) !!} {{ $paymentMethodOption }}</label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="portlet box blue-hoki">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-shopping-cart"></i>
                    <span class="caption-subject">Order Content</span>
                </div>
                <div class="actions">
                    <a href="javascript:;" id="order-clear" class="btn btn-default btn-sm">Clear</a>
                </div>
            </div>
            <div class="portlet-body" id="order-content-wrapper" data-order_edit="{{ isset($editOrder)?$editOrder:false }}">
                <table id="line-items-table" class="table table-hover table-bordered table-striped">
                    <thead>
                    <tr>
                        <th> Item </th>
                        <th style="width: 10%;"> Availability </th>
                        <th style="width: 18%;"> Price </th>
                        <th style="width: 5%;"> Quantity </th>
                        <th style="width: 18%;"> Total </th>
                        <th>  </th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php $shippingLineItems = []; ?>
                        @if($lineItems)
                            @foreach($lineItems as $idx=>$lineItem)
                                @if($lineItem['line_item_type'] == 'fee')
                                    @include('backend.order.line_items.form.fee', ['key' => $idx])
                                @elseif($lineItem['line_item_type'] == 'product')
                                    @include('backend.order.line_items.form.product', ['key' => $idx])
                                @elseif($lineItem['line_item_type'] == 'shipping')
                                    <?php $shippingLineItems[$idx] = $lineItem; ?>
                                @endif
                            @endforeach

                            @foreach($shippingLineItems as $idx=>$shippingLineItem)
                                @include('backend.order.line_items.form.shipping', ['key' => $idx, 'taxable'=> $shippingLineItem['taxable'], 'shipping_method' => $shippingLineItem['shipping_method'], 'shipping_method_id' => $shippingLineItem['line_item_id']])
                            @endforeach
                        @else
                            @include('backend.order.line_items.form.product', ['key' => 0])
                        @endif
                    </tbody>
                </table>
                <div class="clearfix">
                    <a href="#" id ="add-product-lineitem" class="btn btn-default btn-sm"><i class="fa fa-tag"></i> Add Product</a>
                    <a href="#" id="add-fee-lineitem" class="btn btn-default btn-sm"><i class="fa fa-ellipsis-h"></i> Add Fee</a>
                </div>

                <div style="margin-top: 10px;" class="row">
                    <div class="col-md-5">
                        <div id="shipping-options-wrapper" style="display: none;">
                            <div class="input-group input-group-sm">
                                <span class="input-group-btn">
                                    <a class="btn btn-success shipping-select" href="#"><i class="fa fa-check"></i></a>
                                    <a class="btn btn-default shipping-cancel" href="#"><i class="fa fa-remove"></i></a>
                                </span>
                            </div>
                        </div>
                        <a href="#" id="add-shipping-lineitem" class="btn btn-default btn-sm" data-shipping_options="{{ route('backend.sales.order.shipping_options') }}"><i class="fa fa-truck"></i> Add Shipping</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        @include('backend.master.form.fields.textarea', [
            'name' => 'notes',
            'label' => null,
            'key' => 'notes',
            'attr' => [
                'class' => 'form-control',
                'id' => 'notes',
                'rows' => 4,
                'placeholder' => 'Notes'
            ],
        ])

        @if(view()->exists('project::backend.order.additional_fields'))
            @include('project::backend.order.additional_fields')
        @endif
    </div>

    <div class="col-md-4">
        <div class="portlet light bordered" id="coupons-wrapper">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-hand-scissors-o"></i>
                    <span class="caption-subject">Coupons</span>
                </div>
            </div>
            <div class="portlet-body">
                <div class="input-group">
                    {!! Form::text('coupon_code', null, ['placeholder' => 'Coupon Code', 'id' => 'coupon-field', 'class' => 'form-control']) !!}
                    <span class="input-group-btn">
                    <button id="coupon-add-btn" data-coupon_add="{{ route('backend.sales.order.add_coupon') }}" class="btn btn-default" type="button"><i class="fa fa-plus"></i> Add</button>
                </span>
                </div>
                @foreach(old('added_coupons', []) as $idx=>$added_coupon)
                    {!! Form::hidden('added_coupons['.$idx.']', $added_coupon, ['class' => 'added-coupon']) !!}
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="well" id="order-summary">
            <div class="row static-info align-reverse subtotal">
                <div class="col-md-8 name"> Sub Total: </div>
                <div class="col-md-4 value"> <span class="currency-symbol">{{ CurrencyHelper::getCurrentCurrency()['symbol'] }}</span> <span class="amount">0</span> </div>
            </div>
            <!--
            <div class="row static-info align-reverse discount">
                <div class="col-md-8 name"> Discount: </div>
                <div class="col-md-4 value"> <span class="currency-symbol">{{ CurrencyHelper::getCurrentCurrency()['symbol'] }}</span> <span class="amount">0</span> </div>
            </div>
            -->
            <div class="row static-info align-reverse shipping">
                <div class="col-md-8 name"> Shipping: </div>
                <div class="col-md-4 value"> <span class="currency-symbol">{{ CurrencyHelper::getCurrentCurrency()['symbol'] }}</span> <span class="amount">0</span> </div>
            </div>
            <div id="cart-price-rules-wrapper">
                @foreach($cartPriceRules as $idx=>$cartPriceRule)
                    @include('backend.order.line_items.form.cart_price_rule', ['key' => $idx, 'label' => $cartPriceRule->name, 'is_coupon' => $cartPriceRule->isCoupon, 'value' => 0, 'cart_price_rule_id' => $cartPriceRule->id, 'idx' => $idx])
                @endforeach
            </div>
            <div id="tax-summary-wrapper">
                @foreach($taxes as $idx=>$tax)
                    @include('backend.order.line_items.form.tax', ['key' => $idx, 'label' => $tax->getSingleName(), 'value' => 0, 'rate' => $tax->rate, 'tax_id' => $tax->id, 'idx' => $idx])
                @endforeach
            </div>
            <div class="row static-info align-reverse tax-error">
                <div class="col-md-8 name"> Tax Error: </div>
                <div class="col-md-4 value"> <span class="currency-symbol">{{ CurrencyHelper::getCurrentCurrency()['symbol'] }}</span> <span class="amount">0</span> </div>
            </div>
            <div class="row static-info align-reverse rounding">
                <div class="col-md-8 name"> Rounding: </div>
                <div class="col-md-4 value"> <span class="currency-symbol">{{ CurrencyHelper::getCurrentCurrency()['symbol'] }}</span> <span class="amount">0</span> </div>
            </div>
            <div class="row static-info align-reverse total">
                <div class="col-md-8 name"> Grand Total: </div>
                <div class="col-md-4 value"> <span class="currency-symbol">{{ CurrencyHelper::getCurrentCurrency()['symbol'] }}</span> <span class="amount">0</span> </div>
            </div>
        </div>
    </div>
</div>

{!! Form::hidden('store_id', $order->store_id?$order->store_id:ProjectHelper::getActiveStore()->id) !!}
{!! Form::hidden('currency', CurrencyHelper::getCurrentCurrency()['code']) !!}
{!! Form::hidden('backUrl', Request::input('backUrl')) !!}

<div class="modal fade" id="place_order_modal" role="basic" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body">
                <img src="{{ asset('backend/assets/template/global/img/loading-spinner-grey.gif') }}" alt="" class="loading">
                <span> &nbsp;&nbsp;Loading... </span>
            </div>
        </div>
    </div>
</div>

@section('bottom_page_scripts')
    @parent

    <script>
        global_vars.product_line_item = '{{ route('backend.sales.order.line_item.row', ['type' => 'product']) }}';
        global_vars.get_order_cart_rules_path = '{{ route('backend.sales.order.get_cart_rules') }}';
        global_vars.get_tax_path = '{{ route('backend.tax.get') }}';
        global_vars.get_product_availability = '{{ route('backend.catalog.product.availability', ['id' => null]) }}';
        global_vars.get_availability_calendar = '{{ route('catalog.product.availability_calendar') }}';
    </script>

    <script id="lineitem-product-template" type="text/x-handlebars-template">
        @include('backend.order.line_items.form.product', ['key' => '@{{key}}'])
    </script>

    <script id="lineitem-fee-template" type="text/x-handlebars-template">
        @include('backend.order.line_items.form.fee', ['key' => '@{{key}}'])
    </script>

    <script id="lineitem-shipping-template" type="text/x-handlebars-template">
        @include('backend.order.line_items.form.shipping', ['key' => '@{{key}}', 'taxable' => '@{{taxable}}', 'shipping_method' => '@{{shipping_method}}', 'shipping_method_id' => '@{{shipping_method_id}}'])
    </script>

    <script id="lineitem-tax-template" type="text/x-handlebars-template">
        @include('backend.order.line_items.form.tax', ['key' => '@{{key}}', 'label' => '@{{label}}', 'value' => '@{{value}}', 'rate' => '@{{rate}}', 'tax_id' => '@{{tax_id}}'])
    </script>

    <script id="lineitem-cart-price-rule-template" type="text/x-handlebars-template">
        @include('backend.order.line_items.form.cart_price_rule', ['key' => '@{{key}}','label' => '@{{{label}}}', 'value' => '@{{value}}', 'is_coupon' => '@{{is_coupon}}', 'cart_price_rule_id' => '@{{cart_price_rule_id}}'])
    </script>

    <script src="{{ asset('backend/assets/scripts/pages/order_form.js') }}" type="text/javascript"></script>
@stop