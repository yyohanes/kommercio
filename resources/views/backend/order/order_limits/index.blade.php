@extends('backend.master.layout')

@section('top_page_styles')
    <link href="{{ asset('backend/assets/template/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('backend/assets/template/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css') }}" rel="stylesheet" type="text/css" />
@stop

@section('bottom_page_scripts')
    <script src="{{ asset('backend/assets/template/global/scripts/datatable.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js') }}" type="text/javascript"></script>

    <script src="{{ asset('backend/assets/scripts/pages/table_reorder.js') }}" type="text/javascript"></script>
@stop

@section('breadcrumb')
    <li>
        <span>Sales</span>
        <i class="fa fa-circle"></i>
    </li>

    <li>
        <span>{{ \Kommercio\Models\Order\OrderLimit::getTypeOptions($type) }} Order Limit</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase"> {{ \Kommercio\Models\Order\OrderLimit::getTypeOptions($type) }} Order Limit </span>
                </div>
                <div class="actions">
                    @can('access', ['create_order_limit'])
                    <a href="{{ route('backend.order_limit.create', ['type' => $type, 'backUrl' => Request::fullUrl()]) }}" class="btn btn-sm btn-info">
                        <i class="fa fa-plus"></i> Add </a>
                    @endcan
                </div>
            </div>

            <div class="portlet-body">
                <table class="table table-striped table-bordered table-advance dataset-reorder" data-row_class="order_limit-row" data-row_value="order_limit_id"\ data-reorder_action="{{ route('backend.order_limit.reorder', ['type' => $type]) }}">
                    <thead>
                    <tr>
                        <th></th>
                        <th>{{ \Kommercio\Models\Order\OrderLimit::getTypeOptions($type) }}</th>
                        <th>Limit</th>
                        <th>Store</th>
                        <th>Type</th>
                        <th>Date From</th>
                        <th>Date To</th>
                        <th>Active</th>
                        <th style="width: 20%;">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($orderLimits as $orderLimit)
                        <?php
                        $canStoreManage = Gate::allows('manage_store', [$orderLimit]);
                        ?>
                        <tr class="order_limit-row" data-order_limit_id="{{ $orderLimit->id }}">
                            <td>
                                @can('access', ['edit_order_limit'])
                                <i class="fa fa-reorder"></i>
                                @endcan
                            </td>
                            <td>
                                <ul>
                                    @foreach($orderLimit->getItems() as $item)
                                    <li>{{ $item->name }}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td>
                                {{ $orderLimit->limit+0 }}
                            </td>
                            <td>
                                @if($orderLimit->store)
                                    {{ $orderLimit->store->name }}
                                @else
                                    All Stores
                                @endif
                            </td>
                            <td>
                                {{ \Kommercio\Models\Order\OrderLimit::getLimitTypeOptions($orderLimit->limit_type) }}
                            </td>
                            <td>
                                {{ $orderLimit->date_from?$orderLimit->date_from->format('d M Y H:i'):null }}
                            </td>
                            <td>
                                {{ $orderLimit->date_to?$orderLimit->date_to->format('d M Y H:i'):null }}
                            </td>
                            <td> <i class="fa {{ $orderLimit->active?'fa-check text-success':'fa-remove text-danger' }}"></i> </td>
                            <td class="text-center">
                                {!! Form::open(['route' => ['backend.order_limit.delete', 'id' => $orderLimit->id]]) !!}
                                <div class="btn-group btn-group-sm">
                                    @if(Gate::allows('access', ['edit_order_limit']) && $canStoreManage)
                                    <a class="btn btn-default" href="{{ route('backend.order_limit.edit', ['id' => $orderLimit->id, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-pencil"></i> Edit</a>
                                    @endif

                                    @if(Gate::allows('access', ['delete_order_limit']) && $canStoreManage)
                                    <button class="btn btn-default" data-toggle="confirmation" data-original-title="Are you sure?" title=""><i class="fa fa-trash-o"></i> Delete</button>
                                    @endif
                                </div>
                                {!! Form::close() !!}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop