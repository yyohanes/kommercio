@extends('backend.master.form_template')

@section('breadcrumb')
    <li>
        <span>Report</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>{{ $shippingMethod }} on {{ $date->format('l, j M Y') }}</span>
    </li>
@stop

@section('table_content')
    @php
        $total = 0;
        $totalOutstanding = 0;
    @endphp
    @foreach($orders as $idx=>$order)
        <tr>
            <td><input type="checkbox" class="checkboxes" name="id[]" value="{{ $order->id }}" /></td>
            <td>{{ $idx+1 }}</td>
            <td>
                <?php
                $orderAction = '';

                $orderAction .= Form::open(['route' => ['backend.sales.order.delete', 'id' => $order->id, 'backUrl' => Request::fullUrl()], 'class' => 'form-in-btn-group']);
                $orderAction .= '<div class="btn-group btn-group-xs">';

                $orderAction .= '<a class="btn btn-default" href="'.route('backend.sales.order.view', ['id' => $order->id, 'backUrl' => Request::fullUrl()]).'"><i class="fa fa-search"></i></a>';

                if($order->isEditable) {
                    if (Gate::allows('access', ['edit_order'])):
                        $orderAction .= '<a class="btn btn-default" href="' . route('backend.sales.order.edit', ['id' => $order->id, 'backUrl' => Request::fullUrl()]) . '"><i class="fa fa-pencil"></i></a>';
                    endif;

                    if ($order->isDeleteable) {
                        if (Gate::allows('access', ['delete_order'])):
                            $orderAction .= '<button class="btn btn-default" data-toggle="confirmation" data-original-title="Are you sure?" title="" class="btn-link"><i class="fa fa-trash-o"></i></button></div>';
                        endif;
                    }
                }

                $orderAction .= Form::close().'</div>';

                $processActions = '';
                if (Gate::allows('access', ['process_order']) && $order->isProcessable) {
                    $processActions .= '<li><a class="modal-ajax" href="' . route('backend.sales.order.process', ['action' => 'processing', 'id' => $order->id, 'backUrl' => Request::fullUrl()]) . '"><i class="fa fa-toggle-right"></i> Process</a></li>';
                }

                if(Gate::allows('access', ['ship_order']) && $order->isShippable):
                    $processActions .= '<li><a class="modal-ajax" href="' . route('backend.sales.order.process', ['action' => 'shipped', 'id' => $order->id, 'backUrl' => Request::fullUrl()]) . '"><i class="fa fa-truck"></i> Ship</a></li>';
                endif;
                if(Gate::allows('access', ['complete_order']) && $order->isCompleteable):
                    $processActions .= '<li><a class="modal-ajax" href="' . route('backend.sales.order.process', ['action' => 'completed', 'id' => $order->id, 'backUrl' => Request::fullUrl()]) . '"><i class="fa fa-check-circle"></i> Complete</a></li>';
                endif;
                if(Gate::allows('access', ['cancel_order']) && $order->isCancellable):
                    $processActions .= '<li><a class="modal-ajax" href="' . route('backend.sales.order.process', ['action' => 'cancelled', 'id' => $order->id, 'backUrl' => Request::fullUrl()]) . '"><i class="fa fa-remove"></i> Cancel</a></li>';
                endif;

                if(!empty($processActions)){
                    $orderAction .= '<div class="btn-group btn-group-xs dropup"><button type="button" class="btn btn-default hold-on-click dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" aria-expanded="true"><i class="fa fa-flag-o"></i></button><ul class="dropdown-menu" role="menu">'.$processActions.'</ul></div>';
                }

                $printActions = '';
                if(Gate::allows('access', ['print_invoice']) && $order->isPrintable):
                    $printActions .= '<li><a href="' . route('backend.sales.order.print', ['id' => $order->id]) . '" target="_blank">Invoice</a></li>';
                endif;
                if(Gate::allows('access', ['print_delivery_note']) && $order->isPrintable && config('project.enable_delivery_note', false)):
                    $printActions .= '<li><a href="' . route('backend.sales.order.print', ['id' => $order->id, 'type' => 'delivery_note']) . '" target="_blank">Delivery Note</a></li>';
                endif;

                if(!empty($printActions)){
                    $orderAction .= '<div class="btn-group btn-group-xs dropup"><button type="button" class="btn btn-default hold-on-click dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" aria-expanded="true"><i class="fa fa-print"></i></button><ul class="dropdown-menu" role="menu">'.$printActions.'</ul></div>';
                }
                ?>
                {!! $orderAction !!}
            </td>
            <td>
                <strong>{{ $order->reference }}</strong>
                @if (Gate::allows('access', ['complete_order']) && $order->isCompleteable)
                <div style="margin-top: 10px;">
                    <a
                        class="modal-ajax btn btn-md {{ OrderHelper::getOrderStatusLabelClass(\Kommercio\Models\Order\Order::STATUS_COMPLETED) }}"
                        href="{{ route('backend.sales.order.process', ['action' => 'completed', 'id' => $order->id, 'backUrl' => Request::fullUrl()]) }}"
                    >
                        <i class="fa fa-check-circle"></i> Complete
                    </a>
                </div>
                @endif
            </td>
            <td>{!! '<label class="label label-sm bg-'.OrderHelper::getOrderStatusLabelClass($order->status).' bg-font-'.OrderHelper::getOrderStatusLabelClass($order->status).'">'.$order->statusLabel.'</label>' !!}</td>
            <td>
                {{ $order->shipping_full_name }}
                @if($order->shippingInformation->phone_number)
                    <br/><i class="fa fa-phone-square"></i> {{ $order->shippingInformation->phone_number }}
                @endif
                @if($order->shippingInformation->email)
                    <br/><i class="fa fa-envelope-square"></i> {{ $order->shippingInformation->email }}
                @endif
            </td>
            <td>{!! AddressHelper::printAddress($order->shippingInformation->getDetails()) !!}</td>
            @foreach($includedProducts as $includedProduct)
                <td>{{ $order->getProductQuantity($includedProduct['product']->id, true) + 0 }}</td>
            @endforeach
            <td>{!! '<label class="label label-sm label-'.($order->outstanding > 0?'warning':'success').'">'.PriceFormatter::formatNumber($order->outstanding).'</label>' !!}</td>
            <td>{{ PriceFormatter::formatNumber($order->total, $order->currency) }}</td>
            <td>{{ $order->checkout_at?$order->checkout_at->format('d M Y, H:i'):'' }}</td>
            <td>{{ $order->paymentMethod->name }}</td>
            <td>
                @if($order->notes)
                    <p>{!! nl2br($order->notes) !!}</p>
                @endif

                @foreach($order->additional_fields as $additionalFieldKey => $additionalField)
                    <div>
                        <strong>{{ trans(LanguageHelper::getTranslationKey('order.additional_fields.'.$additionalFieldKey)) }}:</strong> {!! nl2br($additionalField) !!}
                    </div>
                @endforeach
            </td>
            @if($filter['shipping_method'] == 'all')
                <td>{{ $order->getShippingLineItem()->getSelectedMethod('name') }}</td>
            @endif
        </tr>
        <?php
        $total += CurrencyHelper::convert($order->total, $order->currency);
        $totalOutstanding += CurrencyHelper::convert($order->outstanding, $order->currency);
        ?>
    @endforeach
@endsection

@section('table_footer')
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    @foreach($includedProducts as $includedProduct)
        <td>{{ $includedProduct['quantity'] + 0 }}</td>
    @endforeach
    <td>{{ PriceFormatter::formatNumber($totalOutstanding) }}</td>
    <td>{{ PriceFormatter::formatNumber($total) }}</td>
    <td></td>
    <td></td>
    @if($filter['shipping_method'] == 'all')
        <td></td>
    @endif
@endsection

@section('header_summary')
    <div class="portlet">
        <div class="portlet-body">
        @foreach($includedProducts as $includedProduct)
            <div>
                <strong>{{ $includedProduct['product']->name }}</strong>: {{ $includedProduct['quantity'] + 0 }}
            </div>
        @endforeach
        </div>
    </div>
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
                    <span class="caption-subject sbold uppercase">{{ $shippingMethod }} on {{ $date->format('l, j M Y') }} ({{ $dateType }})</span>
                </div>
                <div class="actions">
                    {!! Form::close() !!}
                    <a target="_blank" href="{{ $exportUrl }}" class="btn btn-sm btn-info">
                        <i class="fa fa-file-excel-o"></i> Export to XLS</a>
                    @can('access', ['print_invoice'])
                    <a target="_blank" href="{{ $printAllInvoicesUrl }}" class="btn btn-sm btn-info">
                        <i class="fa fa-print"></i> All Invoices</a>
                    @endcan
                    @can('access', ['print_delivery_note'])
                    <a target="_blank" href="{{ $printAllDosUrl }}" class="btn btn-sm btn-info">
                        <i class="fa fa-print"></i> All Delivery Orders</a>
                    @endcan
                </div>
            </div>

            <div class="portlet-body dataset-wrapper">
                @yield('header_summary')

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

                {{-- data-dataset-options='{"scrollX":true,"scrollY":640,"fixedColumns":{"leftColumns":4}}' --}}
                <table id="delivery-table" class="table table-striped table-bordered table-hover dataset-table fixed-header-table" data-dataset-paging="false">
                    <thead>
                    <tr>
                        <th style="width: 10px;">
                            <input type="checkbox" class="group-checkable" data-set="#delivery-table .checkboxes" />
                        </th>
                        <th style="width: 10px;"></th>
                        <th></th>
                        <th>Order #</th>
                        <th>Status</th>
                        <th>Customer</th>
                        <th>Address</th>
                        @foreach($includedProducts as $includedProduct)
                            <th>
                                {{ $includedProduct['product']->name }}
                                <hr/>
                                {{ $includedProduct['quantity'] + 0 }}
                            </th>
                        @endforeach
                        <th>Outstanding</th>
                        <th>Total</th>
                        <th>Purchased On</th>
                        <th>Payment</th>
                        <th>Note</th>
                        @if($filter['shipping_method'] == 'all')
                            <th>Method</th>
                        @endif
                    </tr>
                    </thead>
                    <tbody>
                        @yield('table_content')
                    </tbody>
                    <tfoot>
                        @yield('table_footer')
                    </tfoot>
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
    <script src="{{ asset('backend/assets/template/global/plugins/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js') }}" type="text/javascript"></script>

    <script src="{{ asset('backend/assets/scripts/pages/table_reorder.js') }}" type="text/javascript"></script>
@stop
