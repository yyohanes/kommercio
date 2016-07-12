@extends('backend.master.form_template')

@section('breadcrumb')
    <li>
        <span>Report</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Production Schedule for {{ $deliveryDate->format('l, j M Y') }}</span>
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
                    <span class="caption-subject sbold uppercase">Production Schedule for {{ $deliveryDate->format('l, j M Y') }}</span>
                </div>
            </div>

            <div class="portlet-body">
                @foreach($orders as $idx=>$order)
                    <table class="table-cell-valign-middle table table-bordered margin-bottom-15">
                        <tbody>
                        <tr>
                            <td class="text-center" rowspan="100" style="width: 50px;">
                                {{ $idx + 1 }}
                            </td>
                            <td style="width: 40%;"><strong>Order Number</strong></td>
                            <td colspan="2"><strong>{{ $order->reference }}</strong></td>
                        </tr>
                        <tr>
                            <td>Product</td>
                            <td>Quantity</td>
                            <td style="width: 30%;">Notes</td>
                        </tr>
                        @foreach($order->getProductLineItems() as $idy=>$productLineItem)
                            <tr>
                                <td>
                                    {!! $productLineItem->product->hasThumbnail()?'<img style="width: 80px; height: auto;" src="'.asset($productLineItem->product->thumbnail->getImagePath('backend_thumbnail')).'" />':'' !!}
                                    {{ $productLineItem->product->name }}
                                </td>
                                <td>{{ $productLineItem->quantity + 0 }}</td>
                                @if($idy == 0)
                                    <td rowspan="100">{!! nl2br($order->notes) !!}</td>
                                @endif
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endforeach
            </div>
        </div>

        <div class="portlet light portlet-fit bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase">Total</span>
                </div>
            </div>

            <div class="portlet-body">
                <div class="table-scrollable">
                    <table class="table table-striped table-hover">
                        <thead>
                        <tr>
                            <th style="width: 20px;">#</th>
                            <th colspan="2">Product</th>
                            <th>Quantity</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $count = 0; ?>
                        @foreach($orderedProducts as $idx=>$orderedProduct)
                            <?php $count += 1; ?>
                            <tr>
                                <td>{{ $count }}</td>
                                <td style="width: 100px;">{!! $orderedProduct['product']->hasThumbnail()?'<img style="width: 80px; height: auto;" src="'.asset($orderedProduct['product']->thumbnail->getImagePath('backend_thumbnail')).'" />':'' !!}</td>
                                <td>{{ $orderedProduct['product']->name }}</td>
                                <td>{{ $orderedProduct['quantity'] + 0 }}</td>
                            </tr>
                        @endforeach
                        </tbody>
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