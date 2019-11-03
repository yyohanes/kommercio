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

                {!! Form::hidden('user_id', null, ['id' => 'user-id-value']) !!}
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

    @if(count($managedStores) > 1)
    <div class="col-md-3">
        <?php
        $storeOptions = [];
        foreach($managedStores as $managedStore){
            $storeOptions[$managedStore->id] = $managedStore->name;
        }
        ?>
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-shopping-cart"></i>
                    <span class="caption-subject">Store</span>
                </div>
            </div>
            <div class="portlet-body" id="store-selection-wrapper">
                <div class="form-group">
                    <div class="col-md-12">
                        {!! Form::select('store_id', $storeOptions, [$order->store_id?:ProjectHelper::getActiveStore()->id], ['id' => 'store-option', 'class' => 'form-control']) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @else
        <?php
        $onlyStore = $managedStores->get(0);
        ?>
        {!! Form::hidden('store_id', $order->store_id?$order->store_id:$onlyStore->id) !!}
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
                                <?php $paymentMethod = \Kommercio\Models\PaymentMethod\PaymentMethod::findOrFail($value); ?>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-clock-o"></i>
                    <span class="caption-subject">Date Information</span>
                </div>
            </div>
            <div class="portlet-body">
                @if(config('project.enable_delivery_date', FALSE))
                    <div id="delivery-date-panel">
                        @include('backend.master.form.fields.text', [
                            'name' => 'delivery_date',
                            'label' => 'Delivery Date',
                            'key' => 'delivery_date',
                            'attr' => [
                                'class' => 'form-control' . (Gate::allows('access', ['past_months_delivery_date']) ? '' : ' disable-past-months'),
                                'data-date-format' => 'yyyy-mm-dd',
                                'id' => 'delivery_date',
                                'placeholder' => 'YYYY-MM-DD',
                            ],
                            'defaultValue' => old('delivery_date', $order->delivery_date?$order->delivery_date->format('Y-m-d'):null)
                        ])
                    </div>

                    <div id="delivery-date-time">
                        @include('backend.master.form.fields.text', [
                            'name' => 'delivery_time',
                            'label' => 'Delivery Time',
                            'key' => 'delivery_time',
                            'attr' => [
                                'class' => 'form-control',
                                'id' => 'delivery_time',
                                'placeholder' => \Carbon\Carbon::now()->format('H:i:s'),
                            ],
                            'defaultValue' => old('delivery_time', $order->delivery_date ? $order->delivery_date->format('H:i:s') : null),
                            'helpText' => 'This is only relevant for Same Day Delivery',
                        ])
                    </div>
                @endif

                @php
                $invoiceDueDatePresetName = 'invoices[' . ($invoice->id ? : 0) . '][preset]';
                $invoiceDueDatePresetKey = 'invoices.' . ($invoice->id ? : 0) . '.preset';
                $customDueDateParentSelector = '#'.str_replace(']', '\\]', str_replace('[', '\\[',$invoiceDueDatePresetName));

                $invoiceDueDateName = 'invoices[' . ($invoice->id ? : 0) . '][due_date]';
                $invoiceDueDateKey = 'invoices.' . ($invoice->id ? : 0) . '.due_date';
                @endphp

                @include('backend.master.form.fields.select', [
                    'name' => $invoiceDueDatePresetName,
                    'label' => 'Due Date',
                    'key' => $invoiceDueDatePresetKey,
                    'options' => $dueDatePresetOptions,
                    'attr' => [
                        'class' => 'form-control',
                        'id' => $invoiceDueDatePresetName
                    ],
                    'defaultOptions' => old($invoiceDueDatePresetKey, $invoiceDefaultPreset)
                ])

                @include('backend.master.form.fields.text', [
                    'name' => $invoiceDueDateName,
                    'key' => $invoiceDueDateKey,
                    'attr' => [
                        'class' => 'form-control date-picker',
                        'data-date-format' => 'yyyy-mm-dd',
                        'id' => $invoiceDueDateName,
                        'placeholder' => 'YYYY-MM-DD',
                        'data-select_dependent' => $customDueDateParentSelector,
                        'data-select_dependent_value' => 'custom'
                    ],
                    'defaultValue' => old($invoiceDueDateKey, $invoiceDefaultDueDate ? $invoiceDefaultDueDate->format('Y-m-d') : null)
                ])
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

                @if(ProjectHelper::isFeatureEnabled('order.order_limit'))
                <div style="margin-top: 10px;" class="row">
                    <div class="col-md-5">
                        <table id="category-limit-wrapper" class="table">
                            <thead>
                                <tr>
                                    <th>Category Limits</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                @endif
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

        @if(ProjectHelper::isFeatureEnabled('customer.reward_points'))
            <div class="portlet light bordered" id="reward-points-wrapper">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-gift"></i>
                        <span class="caption-subject">Reward Points</span>
                    </div>
                </div>
                <div class="portlet-body">

                </div>
            </div>
        @endif
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

{!! Form::hidden('checkout_at', $order->checkout_at?:null) !!}
{!! Form::hidden('order_id', $order->id) !!}
{!! Form::hidden('currency', CurrencyHelper::getCurrentCurrency()['code'], ['id' => 'currency-input']) !!}
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
        global_vars.get_reward_points_path = '{{ route('backend.customer.reward_rule.get') }}';
        global_vars.get_tax_path = '{{ route('backend.tax.get') }}';
        global_vars.get_product_availability = '{{ route('backend.catalog.product.availability', ['id' => null]) }}';
        global_vars.get_category_availability = '{{ route('backend.sales.order.category_availability') }}';
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

    <script id="reward-rule-template" type="text/x-handlebars-template">
        <div class="row static-info reward-rule" data-reward_rule_id="@{{id}}">
            <div class="col-md-7 name"> @{{name}}: </div>
            <div class="col-md-5 value"> <span class="amount">@{{reward}}</span> </div>
        </div>
    </script>

    <script src="{{ asset('backend/assets/scripts/pages/order_form.js?cb=2') }}" type="text/javascript"></script>
@stop
