@extends('backend.master.form_template')

@section('breadcrumb')
    <li>
        <span>Report</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Shipment</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>{{ $shippingMethod }} on {{ $date->format('l, j M Y') }}</span>
    </li>
@stop

@section('table_content')
    @foreach($deliveryOrders as $idx => $deliveryOrder)
        @php
        $order = $deliveryOrder->order;
        @endphp
        <tr>
            <td>{{ $idx+1 }}</td>
            <td>
                <?php
                $orderAction = '';
                $orderAction .= '<div class="btn-group btn-group-xs">';
                $orderAction .= '<a class="btn btn-default" href="' . route('backend.sales.order.view', ['id' => $deliveryOrder->order_id, 'backUrl' => Request::fullUrl()]) . '"><i class="fa fa-search"></i></a>';
                $orderAction .= '</div>';

                $printActions = '';
                if (Gate::allows('access', ['print_invoice']) && $order->isPrintable):
                    $printActions .= '<li><a href="' . route('backend.sales.order.print', ['id' => $order->id]) . '" target="_blank">Invoice</a></li>';
                endif;

                if ($deliveryOrder->isPrintable):
                    $printActions .= '<li><a href="' . route('backend.sales.order.delivery_order.print', ['id' => $deliveryOrder->id, 'type' => 'packaging_slip']) . '" target="_blank">Packaging Slip</a></li>';
                endif;

                if(!empty($printActions)){
                    $orderAction .= '<div class="btn-group btn-group-xs dropup"><button type="button" class="btn btn-default hold-on-click dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" aria-expanded="true"><i class="fa fa-print"></i></button><ul class="dropdown-menu" role="menu">' . $printActions . '</ul></div>';
                }
                ?>
                {!! $orderAction !!}
            </td>
            <td>
                {{ $order->reference }}
                <div class="expanded-detail" data-ajax_load="{{ route('backend.sales.order.quick_content_view', ['id' => $order->id]) }}"></div>
            </td>
            <td>{{ $deliveryOrder->calculateTotalQuantity() }}</td>
            <td>
                {{ $deliveryOrder->shippingInformation->full_name }}
                @if($deliveryOrder->shippingInformation->phone_number)
                    <br/><i class="fa fa-phone-square"></i> {{ $deliveryOrder->shippingInformation->phone_number }}
                @endif
                @if($deliveryOrder->shippingInformation->email)
                    <br/><i class="fa fa-envelope-square"></i> {{ $deliveryOrder->shippingInformation->email }}
                @endif
            </td>
            <td>{!! AddressHelper::printAddress($deliveryOrder->shippingInformation->getDetails()) !!}</td>
            <td>
                @if(!empty($deliveryOrder->getData('delivered_by', null)))
                    <p>{{ $deliveryOrder->getData('delivered_by') }}</p>
                @endif
                @if(!empty($deliveryOrder->getData('tracking_number', null)))
                    <p>{{ $deliveryOrder->getData('tracking_number') }}</p>
                @endif
            </td>
            <td>{{ $order->checkout_at ? $order->checkout_at->format('d M Y, H:i') : '' }}</td>
            <td>{{ $order->delivery_date ? $order->delivery_date->format('d M Y') : '' }}</td>
            <td>{{ $deliveryOrder->shippingMethodName }}</td>
            <td>{!! '<label class="label label-sm bg-' . OrderHelper::getDeliveryOrderStatusLabelClass($deliveryOrder->status).' bg-font-' . OrderHelper::getDeliveryOrderStatusLabelClass($deliveryOrder->status) . '">' . $deliveryOrder->statusLabel . '</label>' !!}</td>
            <td>{!! '<label class="label label-sm label-' . ($order->outstanding > 0 ? 'warning' : 'success') . '">' . PriceFormatter::formatNumber($order->outstanding) . '</label>' !!}</td>
        </tr>
    @endforeach
@endsection

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-body">
                {!! Form::open(['method' => 'GET']) !!}
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="control-label">Date</label>

                            {!! Form::select('search[date_type]',
                                $dateTypeOptions, old('search.date_type', $filter['date_type']), [
                                'class' => 'form-control select2']) !!}

                            {!! Form::text('search[date]', old('search.date', $filter['date']), [
                                'class' => 'form-control date-picker',
                                'data-date-format' => 'yyyy-mm-dd']) !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="control-label">Shipping Method</label>
                        {!! Form::select('search[shipping_method][]',
                        $shippingMethodOptions, old('search.shipping_method', $filter['shipping_method']), [
                        'class' => 'form-control select2', 'multiple' => TRUE]) !!}
                    </div>
                    <div class="col-md-2">
                        <label class="control-label">Status</label>
                        {!! Form::select('search[status][]',
                        $statusOptions, old('search.status', $filter['status']), [
                        'class' => 'form-control select2', 'multiple' => TRUE]) !!}
                    </div>
                    <div class="col-md-2">
                        <label class="control-label">Store</label>
                        {!! Form::select('search[store]',
                        $storeOptions, old('search.store', $filter['store']), [
                        'class' => 'form-control select2']) !!}
                    </div>
                    <div class="col-md-2">
                        <div>&nbsp;</div>
                        <button class="btn btn-info btn-sm"><i class="fa fa-search"></i> Search</button>
                        <a class="btn btn-default btn-sm" href="{{ route('backend.report.shipment') }}">Reset</a>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>

        <div class="portlet light portlet-fit bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase">{{ $shippingMethod }} on {{ $date->format('l, j M Y') }} ({{ $dateType }})</span>
                </div>
                <div class="actions">
                    @can('access', ['print_invoice'])
                    <a target="_blank" href="{{ $printAllInvoicesUrl }}" class="btn btn-sm btn-info">
                        <i class="fa fa-print"></i> All Invoices</a>
                    @endcan
                    @can('access', ['print_delivery_order'])
                    <a target="_blank" href="{{ $printAllDosUrl }}" class="btn btn-sm btn-info">
                        <i class="fa fa-print"></i> All Delivery Orders</a>
                    @endcan
                    @can('access', ['print_delivery_order'])
                        <a href="{{ $downloadPackagingSlips }}" class="btn btn-sm btn-info">
                            <i class="fa fa-download"></i> Download Packaging Slips</a>
                    @endcan
                </div>
            </div>

            <div class="portlet-body dataset-wrapper">
                <div class="table-group-actions">
                    <span> </span>
                    <select data-bulk_action="{{ route('backend.sales.order.bulk_action') }}" class="table-group-action-input form-control input-inline input-small input-sm">
                        <option value="">Select...</option>
                        @if(Gate::allows('access', ['process_order']))
                            <option value="process:{{ \Kommercio\Models\Order\Order::STATUS_PROCESSING }}">{{ \Kommercio\Models\Order\Order::getStatusOptions(\Kommercio\Models\Order\Order::STATUS_PROCESSING) }}</option>
                        @endif
                        @if(Gate::allows('access', ['ship_order']))
                            <option value="process:{{ \Kommercio\Models\Order\Order::STATUS_SHIPPED }}">{{ \Kommercio\Models\Order\Order::getStatusOptions(\Kommercio\Models\Order\Order::STATUS_SHIPPED) }}</option>
                        @endif
                        @if(Gate::allows('access', ['complete_order']))
                            <option value="process:{{ \Kommercio\Models\Order\Order::STATUS_COMPLETED }}">{{ \Kommercio\Models\Order\Order::getStatusOptions(\Kommercio\Models\Order\Order::STATUS_COMPLETED) }}</option>
                        @endif
                        @if(Gate::allows('access', ['cancel_order']))
                            <option value="process:{{ \Kommercio\Models\Order\Order::STATUS_CANCELLED}}">{{ \Kommercio\Models\Order\Order::getStatusOptions(\Kommercio\Models\Order\Order::STATUS_CANCELLED) }}</option>
                        @endif
                    </select>
                    <button class="btn btn-sm btn-default table-group-action-submit">
                        <i class="fa fa-check"></i> Submit</button>
                </div>

                <table id="delivery-table" class="table table-striped table-bordered table-hover dataset-table fixed-header-table" data-dataset-paging="false">
                    <thead>
                    <tr>
                        <th style="width: 10px;"></th>
                        <th></th>
                        <th>Order #</th>
                        <th>Dispatch Qty</th>
                        <th>Customer</th>
                        <th>Address</th>
                        <th>Tracking</th>
                        <th>Purchased On</th>
                        <th>Delivery Date</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Outstanding</th>
                    </tr>
                    </thead>
                    <tbody>
                    @yield('table_content')
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('top_page_styles')
    @parent

    <link href="{{ asset('backend/assets/template/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets/template/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css') }}" rel="stylesheet" type="text/css" />
@stop

@section('bottom_page_scripts')
    @parent

    <script src="{{ asset('backend/assets/template/global/scripts/datatable.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js') }}" type="text/javascript"></script>

    <script src="{{ asset('backend/assets/scripts/pages/table_reorder.js') }}" type="text/javascript"></script>
@stop
