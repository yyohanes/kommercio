@extends('backend.master.form_template')

@section('breadcrumb')
    <li>
        <span>Report</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>{{ $shippingMethod }} on {{ $deliveryDate->format('l, j M Y') }}</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-body">
                {!! Form::open(['method' => 'GET']) !!}
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label class="control-label">Delivery Date</label>

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
                        $orderStatusOptions, old('search.status', $filter['status']), [
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
                        <a class="btn btn-default btn-sm" href="{{ route('backend.report.delivery') }}">Reset</a>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>

        <div class="portlet light portlet-fit bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase">{{ $shippingMethod }} on {{ $deliveryDate->format('l, j M Y') }}</span>
                </div>
            </div>

            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th style="width: 10px;"></th>
                            <th>Order #</th>
                            <th>Purchased On</th>
                            <th>Status</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Address</th>
                            @foreach($orderedProducts as $orderedProduct)
                            <th>{{ $orderedProduct['name'] }}</th>
                            @endforeach
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
                                <td>{!! '<label class="label label-sm bg-'.OrderHelper::getOrderStatusLabelClass($order->status).' bg-font-'.OrderHelper::getOrderStatusLabelClass($order->status).'">'.$order->statusLabel.'</label>' !!}</td>
                                <td>{{ $order->shipping_full_name }}</td>
                                <td>{{ $order->shippingInformation->phone_number }}</td>
                                <td>{{ $order->shippingInformation->email }}</td>
                                <td>{!! AddressHelper::printAddress($order->shippingInformation->getDetails()) !!}</td>
                                @foreach($orderedProducts as $orderedProductIdx=>$orderedProduct)
                                    <td>{{ $order->getProductQuantity($orderedProductIdx) + 0 }}</td>
                                @endforeach
                                <td>{{ PriceFormatter::formatNumber($order->total, $order->currency) }}</td>
                                <td>{{ $order->paymentMethod->name }}</td>
                                <td>{!! nl2br($order->notes) !!}</td>
                                @if($filter['shipping_method'] == 'all')
                                    <td>{{ $order->getShippingLineItem()->getSelectedMethod('name') }}</td>
                                @endif
                            </tr>
                            <?php $total += CurrencyHelper::convert($order->total, $order->currency); ?>
                        @endforeach
                        </tbody>
                        <tfoot>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            @foreach($orderedProducts as $orderedProductIdx=>$orderedProduct)
                                <td>{{ $orderedProduct['quantity'] + 0 }}</td>
                            @endforeach
                            <td>{{ PriceFormatter::formatNumber($total) }}</td>
                            <td></td>
                            <td></td>
                            @if($filter['shipping_method'] == 'all')
                                <td></td>
                            @endif
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('bottom_page_scripts')
    @parent

    <script src="{{ asset('backend/assets/scripts/pages/report.js') }}" type="text/javascript"></script>
@stop