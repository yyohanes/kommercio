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
                    @if(Gate::allows('access', ['edit_order']))
                        <a href="{{ route('backend.sales.order.edit', ['id' => $order->id, 'backUrl' => Request::fullUrl()]) }}" class="btn btn-info"><i class="fa fa-pencil"></i> Edit </a>
                    @endif

                    @if(Gate::allows('access', ['process_order']) && $order->isProcessable)
                        <a class="btn {{ OrderHelper::getOrderStatusLabelClass(\Kommercio\Models\Order\Order::STATUS_PROCESSING) }} modal-ajax" href="{{ route('backend.sales.order.process', ['action' => 'processing', 'id' => $order->id, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-toggle-right"></i> Process Order</a>
                    @endif

                    @if(Gate::allows('access', ['ship_order']) && $order->isShippable)
                        <a class="btn {{ OrderHelper::getOrderStatusLabelClass(\Kommercio\Models\Order\Order::STATUS_SHIPPED) }} modal-ajax" href="{{ route('backend.sales.order.process', ['action' => 'shipped', 'id' => $order->id, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-truck"></i> Ship Order</a>
                    @endif

                    @if(Gate::allows('access', ['complete_order']) && $order->isCompleteable)
                        <a class="btn {{ OrderHelper::getOrderStatusLabelClass(\Kommercio\Models\Order\Order::STATUS_COMPLETED) }} modal-ajax" href="{{ route('backend.sales.order.process', ['action' => 'completed', 'id' => $order->id, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-check-circle"></i> Complete Order</a>
                    @endif

                    @if(Gate::allows('access', ['cancel_order']) && $order->isCancellable)
                        <a class="btn {{ OrderHelper::getOrderStatusLabelClass(\Kommercio\Models\Order\Order::STATUS_CANCELLED) }} modal-ajax" href="{{ route('backend.sales.order.process', ['action' => 'cancelled', 'id' => $order->id, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-remove"></i> Cancel Order</a>
                    @endif

                    <?php
                        $printActions = '';

                        if(Gate::allows('access', ['print_invoice']) && $order->isPrintable){
                            $printActions .= '<li><a href="'.route('backend.sales.order.print', ['id' => $order->id]).'" target="_blank">Print Invoice</a></li>';
                        }

                        if(Gate::allows('access', ['print_delivery_note']) && $order->isPrintable && config('project.enable_delivery_note', false)){
                            $printActions .= '<li><a href="'.route('backend.sales.order.print', ['id' => $order->id, 'type' => 'delivery_note']).'" target="_blank">Print Delivery Note</a></li>';
                        }
                    ?>

                    @if(!empty($printActions))
                    <div class="btn-group">
                        <button type="button" class="btn btn-default dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">
                            <i class="fa fa-print"></i> Print
                            <i class="fa fa-angle-down"></i>
                        </button>
                        <ul class="dropdown-menu pull-right">
                            {!! $printActions !!}
                        </ul>
                    </div>
                    @endif

                    <?php
                    $resendActions = '';

                    if(Gate::allows('access', ['resend_order_email'])){
                        if(in_array($order->status, [\Kommercio\Models\Order\Order::STATUS_PENDING])){
                            $resendActions .= '<li><a class="modal-ajax" href="'.route('backend.sales.order.resend_email', ['process' => 'confirmation', 'backUrl' => Request::fullUrl(), 'id' => $order->id]).'" target="_blank">Confirmation</a></li>';
                        }

                        if(in_array($order->status, [\Kommercio\Models\Order\Order::STATUS_PROCESSING])){
                            $resendActions .= '<li><a class="modal-ajax" href="'.route('backend.sales.order.resend_email', ['process' => 'processing', 'backUrl' => Request::fullUrl(), 'id' => $order->id]).'" target="_blank">Processing</a></li>';
                        }

                        if(in_array($order->status, [\Kommercio\Models\Order\Order::STATUS_COMPLETED])){
                            $resendActions .= '<li><a class="modal-ajax" href="'.route('backend.sales.order.resend_email', ['process' => 'completed', 'backUrl' => Request::fullUrl(), 'id' => $order->id]).'" target="_blank">Completed</a></li>';
                        }

                        if(in_array($order->status, [\Kommercio\Models\Order\Order::STATUS_CANCELLED])){
                            $resendActions .= '<li><a class="modal-ajax" href="'.route('backend.sales.order.resend_email', ['process' => 'cancelled', 'backUrl' => Request::fullUrl(), 'id' => $order->id]).'" target="_blank">Cancelled</a></li>';
                        }
                    }
                    ?>

                    @if(!empty($resendActions))
                        <div class="btn-group">
                            <button type="button" class="btn btn-default dropdown-toggle" data-hover="dropdown" data-toggle="dropdown">
                                <i class="fa fa-envelope"></i> Resend
                                <i class="fa fa-angle-down"></i>
                            </button>
                            <ul class="dropdown-menu pull-right">
                                {!! $resendActions !!}
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            <div class="portlet-body">
                <div class="form-body" id="order-wrapper">
                    <div class="tabbable-bordered">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="active" role="presentation">
                                <a href="#tab_details" data-toggle="tab"> Details </a>
                            </li>

                            @can('access', ['view_delivery_order'])
                                <li role="presentation">
                                    <a href="#tab_delivery_orders" data-toggle="tab"> Delivery Orders </a>
                                </li>
                            @endcan

                            @can('access', ['view_payment'])
                            <li role="presentation">
                                <a href="#tab_invoice_payments" data-toggle="tab"> Invoice / Payments </a>
                            </li>
                            @endcan

                            @can('access', ['view_order_external_memo'])
                                <li role="presentation">
                                    <a href="#tab_external_memo" data-toggle="tab"> External Memo </a>
                                </li>
                            @endcan

                            @can('access', ['view_order_internal_memo'])
                            <li role="presentation">
                                <a href="#tab_internal_memo" data-toggle="tab"> Internal Memo </a>
                            </li>
                            @endcan
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
                                                        <div class="col-md-7 value"> {{ $shippingLineItem->name }} </div>
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
                                                        <th> </th>
                                                        <th> Item </th>
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
                                                                @include('backend.order.line_items.view.product', ['key' => $idx, 'lineItem' => $lineItem, 'child' => false])
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

                                        @foreach($order->additional_fields as $additionalFieldKey => $additionalField)
                                            <div class="row static-info">
                                                <div class="col-md-5 name"> {{ trans(LanguageHelper::getTranslationKey('order.additional_fields.'.$additionalFieldKey)) }}: </div>
                                                <div class="col-md-7 value"> {!! nl2br($additionalField) !!} </div>
                                            </div>
                                        @endforeach
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

                                                @foreach($order->getCouponLineItems() as $couponLineItem)
                                                    <div class="row static-info align-reverse tax">
                                                        <div class="col-md-8 name"> {{ $couponLineItem->getPrintName() }}: </div>
                                                        <div class="col-md-4 value"> {{ PriceFormatter::formatNumber($couponLineItem->total, $order->currency) }} </div>
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
                                                        <div class="col-md-4 value"> {{ PriceFormatter::formatNumber($taxLineItem->base_price, $order->currency) }} </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <?php
                                            $taxError = $order->tax_error_total;
                                            ?>
                                            @if(!empty($taxError+0))
                                                <div class="row static-info align-reverse total">
                                                    <div class="col-md-8 name"> Tax Error: </div>
                                                    <div class="col-md-4 value"> {{ PriceFormatter::formatNumber($taxError, $order->currency) }} </div>
                                                </div>
                                            @endif

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

                            @can('access', ['view_delivery_order'])
                                <div class="tab-pane" id="tab_delivery_orders">
                                    <div class="form-body">
                                        @if($order->deliveryOrders->count() > 0)
                                            <div class="margin-bottom-10">
                                                <div class="portlet">
                                                    <div class="portlet-title">
                                                        <div class="caption">
                                                            <i class="fa fa-truck"></i>
                                                            <span class="caption-subject">Delivery Orders</span>
                                                        </div>
                                                    </div>
                                                    <div class="portlet-body">
                                                        <table class="table table-hover">
                                                            <thead>
                                                            <tr>
                                                                <th> </th>
                                                                <th> No. </th>
                                                                <th> Total Qty </th>
                                                                <th> Status </th>
                                                                <th> Date </th>
                                                                <th> Tracking No </th>
                                                                <th> Shipper </th>
                                                                <th> Issued by </th>
                                                                <th></th>
                                                            </tr>
                                                            </thead>
                                                            <tbody id="delivery-order-index-wrapper">
                                                            @php
                                                            $count = 0;
                                                            @endphp
                                                            @foreach($order->deliveryOrders->sortByDesc('created_at') as $idx => $deliveryOrder)
                                                                @php
                                                                $count += 1;
                                                                $doEditable = Gate::allows('access', ['edit_delivery_order']);
                                                                @endphp
                                                                <tr>
                                                                    <td>{{ $count }}</td>
                                                                    <td>{{ $deliveryOrder->reference }}</td>
                                                                    <td>{{ ProjectHelper::formatNumber($deliveryOrder->total_quantity) }}</td>
                                                                    <td>
                                                                        @php
                                                                            $doShippable = $deliveryOrder->isShippable;
                                                                            $doCancellable = $deliveryOrder->isCancellable;
                                                                        @endphp
                                                                        <div class="btn-group">
                                                                            <span href="#" class="btn btn-sm {{ OrderHelper::getDeliveryOrderStatusLabelClass($deliveryOrder->status) }}">{{ \Kommercio\Models\Order\DeliveryOrder\DeliveryOrder::getStatusOptions($deliveryOrder->status) }}</span>
                                                                            @if($doCancellable || $doShippable)
                                                                            <button type="button" class="btn btn-sm {{ OrderHelper::getDeliveryOrderStatusLabelClass($deliveryOrder->status) }} dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                                                                                <i class="fa fa-angle-down"></i>
                                                                            </button>
                                                                            <ul class="dropdown-menu" role="menu">
                                                                                @if($doShippable)
                                                                                <li>
                                                                                    <a href="{{ route('backend.sales.order.delivery_order.quick_status_update', ['id' => $deliveryOrder->id, 'status' => \Kommercio\Models\Order\DeliveryOrder\DeliveryOrder::STATUS_SHIPPED, 'backUrl' => Request::fullUrl()]) }}" class="modal-ajax">Shipped</a>
                                                                                </li>
                                                                                @endif
                                                                                @if($doShippable)
                                                                                <li>
                                                                                    <a href="{{ route('backend.sales.order.delivery_order.quick_status_update', ['id' => $deliveryOrder->id, 'status' => \Kommercio\Models\Order\DeliveryOrder\DeliveryOrder::STATUS_CANCELLED, 'backUrl' => Request::fullUrl()]) }}" class="modal-ajax">Cancel</a>
                                                                                </li>
                                                                                @endif
                                                                            </ul>
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                    <td>{{ $deliveryOrder->created_at->format('d M Y H:i') }}</td>
                                                                    <td>
                                                                        @if(!empty($deliveryOrder->getData('tracking_number', null)))
                                                                            {{ $deliveryOrder->getData('tracking_number') }}
                                                                        @else
                                                                            -
                                                                        @endif
                                                                    </td>
                                                                    <td>
                                                                        @if(!empty($deliveryOrder->getData('delivered_by', null)))
                                                                            {{ $deliveryOrder->getData('delivered_by') }}
                                                                        @else
                                                                            -
                                                                        @endif
                                                                    </td>
                                                                    <td>{{ $deliveryOrder->createdBy?$deliveryOrder->createdBy->email:null }}</td>
                                                                    <td>
                                                                        <div class="btn-group btn-group-xs">
                                                                            <a href="#" class="btn btn-default delivery-order-view-btn" data-delivery_order_id="{{ $deliveryOrder->id }}"><i class="fa fa-search"></i> View</a>
                                                                            @if($doEditable)
                                                                                <a href="{{ route('backend.sales.order.delivery_order.mini_form', ['id' => $deliveryOrder->id, 'backUrl' => Request::fullUrl()]) }}" class="btn btn-default modal-ajax"><i class="fa fa-pencil"></i> Edit</a>
                                                                            @endif
                                                                            @if(!in_array($deliveryOrder->status, [\Kommercio\Models\Order\DeliveryOrder\DeliveryOrder::STATUS_CANCELLED]))
                                                                                <a href="{{ route('backend.sales.order.delivery_order.print', ['id' => $deliveryOrder->id]) }}" class="btn btn-default" target="_blank"><i class="fa fa-print"></i> Print</a>
                                                                            @endif
                                                                            @if($deliveryOrder->isPrintable)
                                                                                <a href="{{ route('backend.sales.order.delivery_order.print', ['id' => $deliveryOrder->id, 'type' => 'packaging_slip']) }}" class="btn btn-default" target="_blank"><i class="fa fa-print"></i> Packaging Slip</a>
                                                                            @endif
                                                                            @if(Gate::allows('access', ['resend_order_email']) && in_array($deliveryOrder->status, [\Kommercio\Models\Order\DeliveryOrder\DeliveryOrder::STATUS_SHIPPED]))
                                                                                <a class="btn btn-default modal-ajax" href="{{  route('backend.sales.order.delivery_order.resend_email', ['id' => $deliveryOrder->id, 'process' => \Kommercio\Models\Order\DeliveryOrder\DeliveryOrder::STATUS_SHIPPED, 'backUrl' => Request::fullUrl()])}}" target="_blank"><i class="fa fa-envelope-o"></i> Resend Email</a>
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <tr id="delivery-order-{{ $deliveryOrder->id }}-view" class="delivery-order-view-row">
                                                                    <td colspan="100">
                                                                        @include('backend.order.delivery_orders.mini_view', ['deliveryOrder' => $deliveryOrder])
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endcan

                            @can('access', ['view_payment'])
                            <div class="tab-pane" id="tab_invoice_payments">
                                <div class="form-body">
                                    @if($order->invoices->count() > 0)
                                    <div class="margin-bottom-10">
                                        <div class="portlet">
                                            <div class="portlet-title">
                                                <div class="caption">
                                                    <i class="fa fa-file-o"></i>
                                                    <span class="caption-subject">Invoice</span>
                                                </div>
                                            </div>
                                            <div class="portlet-body">
                                                <div class="table-scrollable">
                                                    <table class="table table-hover">
                                                        <thead>
                                                        <tr>
                                                            <th colspan="2"> Invoice No </th>
                                                            <th> Amount </th>
                                                            <th> Status </th>
                                                            <th> Date </th>
                                                            <th> Due Date </th>
                                                            <th></th>
                                                        </tr>
                                                        </thead>
                                                        <tbody id="invoice-index-wrapper">
                                                        @foreach($order->invoices as $idx => $invoice)
                                                            <tr>
                                                                <td>{{ $idx + 1 }}</td>
                                                                <td>{{ $invoice->reference }}</td>
                                                                <td>{{ \Kommercio\Facades\PriceFormatter::formatNumber($invoice->total) }}</td>
                                                                <td>
                                                                    <span class="label label-{{ $invoice->status == \Kommercio\Models\Order\Invoice::STATUS_PAID?'success':'warning' }}">{{ \Kommercio\Models\Order\Invoice::getStatusOptions($invoice->status) }}</span>
                                                                </td>
                                                                <td>{{ $invoice->created_at->format('d M Y H:i') }}</td>
                                                                <td>
                                                                    @if($invoice->due_date)
                                                                    <span class="{{ $invoice->isOverdue() ? 'text-danger' : '' }}">{{ $invoice->due_date->format('d M Y') }}</span>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    <a href="{{ route('frontend.order.invoice.view', ['public_id' => $invoice->public_id]) }}" class="btn btn-xs btn-default" target="_blank"><i class="fa fa-search"></i> View</a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif

                                    <hr/>

                                    <div class="margin-bottom-10">
                                        @can('access', ['create_payment'])
                                        <a class="btn btn-default" id="payment-add-btn" href="#">
                                            <i class="icon-plus"></i> Add Payment
                                        </a>
                                        @endcan
                                    </div>

                                    <div id="payment-form-wrapper"
                                         data-payment_form="{{ route('backend.sales.order.payment.form', ['order_id' => $order->id]) }}"
                                         data-payment_index="{{ route('backend.sales.order.payment.index', ['order_id' => $order->id]) }}"></div>

                                    <div class="table-scrollable">
                                        <table class="table table-hover">
                                            <thead>
                                            <tr>
                                                <th> Invoice </th>
                                                <th> Payment Date </th>
                                                <th> Amount </th>
                                                <th> Status </th>
                                                <th> Attachments </th>
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
                            @endcan

                            @can('access', ['view_order_external_memo'])
                                <div class="tab-pane" id="tab_external_memo">
                                    <div class="form-body">
                                        <div class="margin-bottom-10">
                                            @can('access', ['create_order_external_memo'])
                                                <a class="btn btn-default" id="external-memo-add-btn" href="#">
                                                    <i class="icon-plus"></i> Add External Memo
                                                </a>
                                            @endcan
                                        </div>

                                        <div id="external-memo-form-wrapper"
                                             data-external_memo_form="{{ route('backend.sales.order.external_memo.form', ['order_id' => $order->id]) }}"
                                             data-external_memo_index="{{ route('backend.sales.order.external_memo.index', ['order_id' => $order->id]) }}"></div>

                                        <div class="table-scrollable">
                                            <table class="table table-striped table-hover">
                                                <thead>
                                                <tr>
                                                    <th style="width: 60%;">Memo</th>
                                                    <th>By</th>
                                                    <th>Date</th>
                                                    <th></th>
                                                </tr>
                                                </thead>
                                                <tbody id="external-memo-index-wrapper">
                                                @include('backend.order.memos.external.index')
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endcan

                            @can('access', ['view_order_internal_memo'])
                            <div class="tab-pane" id="tab_internal_memo">
                                <div class="form-body">
                                    <div class="margin-bottom-10">
                                        @can('access', ['create_order_internal_memo'])
                                        <a class="btn btn-default" id="internal-memo-add-btn" href="#">
                                            <i class="icon-plus"></i> Add Internal Memo
                                        </a>
                                        @endcan
                                    </div>

                                    <div id="internal-memo-form-wrapper"
                                         data-internal_memo_form="{{ route('backend.sales.order.internal_memo.form', ['order_id' => $order->id]) }}"
                                         data-internal_memo_index="{{ route('backend.sales.order.internal_memo.index', ['order_id' => $order->id]) }}"></div>

                                    <div class="table-scrollable">
                                        <table class="table table-striped table-hover">
                                            <thead>
                                            <tr>
                                                <th style="width: 60%;">Memo</th>
                                                <th>By</th>
                                                <th>Date</th>
                                            </tr>
                                            </thead>
                                            <tbody id="internal-memo-index-wrapper">
                                            @include('backend.order.memos.internal.index')
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endcan
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
