@extends('backend.master.form_template')

@section('breadcrumb')
    <li>
        <span>Sales</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <a href="{{ route('backend.sales.order.index') }}"><span>Order</span></a>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Order #{{ $order->reference }}</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="margin-bottom-10"><a href="{{ NavigationHelper::getBackUrl() }}" class="btn btn-default"><i class="fa fa-arrow-left"></i> Back</a></div>

        <div class="portlet light portlet-fit portlet-form bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase">Order #{{ $order->reference }}</span>
                </div>
                <div class="actions">
                    @if(!$order->status || in_array($order->status, [\Kommercio\Models\Order\Order::STATUS_ADMIN_CART]))
                        <a href="{{ route('backend.sales.order.edit', ['id' => $order->id, 'backUrl' => Request::fullUrl()]) }}" class="btn btn-info"><i class="fa fa-pencil"></i> Edit </a>
                    @endif

                    @if(in_array($order->status, [\Kommercio\Models\Order\Order::STATUS_PENDING]))
                        <a class="btn {{ OrderHelper::getOrderStatusLabelClass(\Kommercio\Models\Order\Order::STATUS_PROCESSING) }} modal-ajax" href="{{ route('backend.sales.order.process', ['action' => 'processing', 'id' => $order->id, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-toggle-right"></i> Process Order</a>
                    @endif

                    @if(in_array($order->status, [\Kommercio\Models\Order\Order::STATUS_PENDING, \Kommercio\Models\Order\Order::STATUS_PROCESSING]))
                        <a class="btn {{ OrderHelper::getOrderStatusLabelClass(\Kommercio\Models\Order\Order::STATUS_COMPLETED) }} modal-ajax" href="{{ route('backend.sales.order.process', ['action' => 'completed', 'id' => $order->id, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-check-circle"></i> Complete Order</a>
                        <a class="btn {{ OrderHelper::getOrderStatusLabelClass(\Kommercio\Models\Order\Order::STATUS_CANCELLED) }} modal-ajax" href="{{ route('backend.sales.order.process', ['action' => 'cancelled', 'id' => $order->id, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-remove"></i> Cancel Order</a>
                        <a class="btn btn-info" href="{{ route('backend.sales.order.print', ['id' => $order->id]) }}" target="_blank"><i class="fa fa-print"></i> Print Order</a>
                    @endif
                </div>
            </div>

            <div class="portlet-body">
                <div class="form-body">
                    <div class="tabbable-bordered">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="active" role="presentation">
                                <a href="#tab_details" data-toggle="tab"> Details </a>
                            </li>
                            <li role="presentation">
                                <a href="#tab_payments" data-toggle="tab"> Payments </a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane active" id="tab_details">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="portlet light bordered">
                                            <div class="portlet-title">
                                                <div class="caption">
                                                    <i class="fa fa-truck"></i>
                                                    <span class="caption-subject">Order Information</span>
                                                </div>
                                            </div>
                                            <div class="portlet-body">
                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> Order #: </div>
                                                    <div class="col-md-7 value"> {{ $order->reference }} </div>
                                                </div>

                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> Placed on: </div>
                                                    <div class="col-md-7 value"> {{ $order->checkout_at?$order->checkout_at->format('D, d M Y'):null }} </div>
                                                </div>

                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> Status: </div>
                                                    <div class="col-md-7 value"> <span class="label bg-{{ OrderHelper::getOrderStatusLabelClass($order->status) }} bg-font-{{ OrderHelper::getOrderStatusLabelClass($order->status) }}">{{ \Kommercio\Models\Order\Order::getStatusOptions($order->status, true) }}</span> </div>
                                                </div>

                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> Grand Total: </div>
                                                    <div class="col-md-7 value"> {{ PriceFormatter::formatNumber($order->total, $order->currency) }} </div>
                                                </div>

                                                @if($order->paymentMethod)
                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> Payment Method: </div>
                                                    <div class="col-md-7 value"> {{ $order->paymentMethod->name }} </div>
                                                </div>
                                                @endif

                                                @if(config('project.enable_delivery_date', FALSE))
                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> Delivery Date: </div>
                                                    <div class="col-md-7 value"> {!! $order->delivery_date?'<span class="label bg-grey bg-font-grey"><strong>'.$order->delivery_date->format('D, d M Y').'</strong></span>':null !!} </div>
                                                </div>
                                                @endif

                                                <?php $shippingLineItem = $order->getShippingLineItem(); ?>
                                                @if($shippingLineItem)
                                                    <div class="row static-info">
                                                        <div class="col-md-5 name"> Shipping: </div>
                                                        <div class="col-md-7 value"> {{ $shippingLineItem->getSelectedMethod()['name'] }} </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="clearfix"></div>

                                    <div class="col-md-6">
                                        <div class="portlet light bordered">
                                            <div class="portlet-title">
                                                <div class="caption">
                                                    <i class="fa fa-user"></i>
                                                    <span class="caption-subject">Customer Information</span>
                                                </div>
                                            </div>
                                            <div class="portlet-body">
                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> Email: </div>
                                                    <div class="col-md-7 value"> {{ $billingProfile->email }} </div>
                                                </div>

                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> Name: </div>
                                                    <div class="col-md-7 value"> {{ $billingProfile->full_name }} </div>
                                                </div>

                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> Phone Number: </div>
                                                    <div class="col-md-7 value"> {{ $billingProfile->phone_number }} </div>
                                                </div>

                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> Address: </div>
                                                    <div class="col-md-7 value"> {!! AddressHelper::printAddress($billingProfile->getDetails()) !!} </div>
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
                                            </div>
                                            <div class="portlet-body">
                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> Email: </div>
                                                    <div class="col-md-7 value"> {{ $shippingProfile->email }} </div>
                                                </div>

                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> Name: </div>
                                                    <div class="col-md-7 value"> {{ $shippingProfile->full_name }} </div>
                                                </div>

                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> Phone Number: </div>
                                                    <div class="col-md-7 value"> {{ $shippingProfile->phone_number }} </div>
                                                </div>

                                                <div class="row static-info">
                                                    <div class="col-md-5 name"> Address: </div>
                                                    <div class="col-md-7 value"> {!! AddressHelper::printAddress($shippingProfile->getDetails()) !!} </div>
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
                                            </div>
                                            <div class="portlet-body" id="order-content-wrapper">
                                                <table id="line-items-table" class="table table-hover table-bordered table-striped">
                                                    <thead>
                                                    <tr>
                                                        <th> Item </th>
                                                        <th style="width: 20%;"> Original Price </th>
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
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        @if(!empty($order->notes))
                                        <div class="well"><strong>Notes</strong><br/>
                                            {!! nl2br($order->notes) !!}
                                        </div>
                                        @endif
                                    </div>

                                    <div class="col-md-6">
                                        <div class="well" id="order-summary">
                                            <div class="row static-info align-reverse subtotal">
                                                <div class="col-md-8 name"> Sub Total: </div>
                                                <div class="col-md-4 value"> {{ PriceFormatter::formatNumber($order->subtotal + $order->additional_total, $order->currency) }} </div>
                                            </div>
                                            <!--
                                            <div class="row static-info align-reverse discount">
                                                <div class="col-md-8 name"> Discount: </div>
                                                <div class="col-md-4 value"> {{ PriceFormatter::formatNumber($order->discount_total, $order->currency) }} </div>
                                            </div>
                                            -->
                                            <div id="cart-price-rules-wrapper">
                                                @foreach($order->getCartPriceRuleLineItems() as $cartPriceRuleLineItem)
                                                    <div class="row static-info align-reverse tax">
                                                        <div class="col-md-8 name"> {{ $cartPriceRuleLineItem->getPrintName() }}: </div>
                                                        <div class="col-md-4 value"> {{ PriceFormatter::formatNumber($cartPriceRuleLineItem->total, $order->currency) }} </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="row static-info align-reverse shipping">
                                                <div class="col-md-8 name"> Shipping: </div>
                                                <div class="col-md-4 value"> {{ PriceFormatter::formatNumber($order->shipping_total, $order->currency) }} </div>
                                            </div>

                                            <div id="tax-summary-wrapper">
                                                @foreach($order->getTaxLineItems() as $taxLineItem)
                                                    <div class="row static-info align-reverse tax">
                                                        <div class="col-md-8 name"> {{ $taxLineItem->name }}: </div>
                                                        <div class="col-md-4 value"> {{ PriceFormatter::formatNumber($taxLineItem->total, $order->currency) }} </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            @if($order->rounding_total > 0 || $order->rounding_total < 0)
                                            <div class="row static-info align-reverse rounding">
                                                <div class="col-md-8 name"> Rounding: </div>
                                                <div class="col-md-4 value"> {{ PriceFormatter::formatNumber($order->rounding_total, $order->currency) }} </div>
                                            </div>
                                            @endif
                                            <div class="row static-info align-reverse total">
                                                <div class="col-md-8 name"> Grand Total: </div>
                                                <div class="col-md-4 value"> {{ PriceFormatter::formatNumber($order->total, $order->currency) }} </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane" id="tab_payments">
                                <div class="form-body">
                                    <div class="margin-bottom-10">
                                        <a class="btn btn-default" id="payment-add-btn" href="#">
                                            <i class="icon-plus"></i> Add Payment
                                        </a>
                                    </div>

                                    <div id="payment-form-wrapper"
                                         data-payment_form="{{ route('backend.sales.order.payment.form', ['order_id' => $order->id]) }}"
                                         data-payment_index="{{ route('backend.sales.order.payment.index', ['order_id' => $order->id]) }}"></div>

                                    <div class="table-scrollable">
                                        <table class="table table-hover">
                                            <thead>
                                            <tr>
                                                <th> Payment Date </th>
                                                <th> Amount </th>
                                                <th> Status </th>
                                                <th> History </th>
                                                <th> </th>
                                            </tr>
                                            </thead>
                                            <tbody id="payment-index-wrapper">
                                            <?php $payments = $order->payments; ?>
                                            @include('backend.order.payments.index')
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('bottom_page_scripts')
    @parent

    <script src="{{ asset('backend/assets/scripts/pages/order_view.js') }}" type="text/javascript"></script>
@stop