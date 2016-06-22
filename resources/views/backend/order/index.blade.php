@extends('backend.master.form_template')

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

    <script type="text/javascript">
        var additional_columns = {{ $stickyProducts->count() }};
    </script>

    <script src="{{ asset('backend/assets/scripts/pages/order_index.js') }}" type="text/javascript"></script>
@stop

@section('breadcrumb')
    <li>
        <span>Sales</span>
        <i class="fa fa-circle"></i>
    </li>

    <li>
        <span>Order</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase"> Orders </span>
                </div>
                <div class="actions">
                    @can('access', ['create_order'])
                    <a href="{{ route('backend.sales.order.create', ['backUrl' => Request::fullUrl()]) }}" class="btn btn-sm btn-info">
                        <i class="fa fa-plus"></i> Add </a>
                    @endcan
                </div>
            </div>

            <div class="portlet-body">
                <table class="table table-striped table-bordered table-advance" id="orders-dataset" data-src="{{ route('backend.sales.order.index') }}" data-form_token="{{ csrf_token() }}">
                    <thead>
                    <tr role="row" class="heading">
                        <th style="width: 10px;"></th>
                        <th>Order #</th>
                        <th>Purchased On</th>
                        @if(config('project.enable_delivery_date', FALSE))
                        <th>Delivery Date</th>
                        @endif
                        <th>Customer</th>
                        <th>Recipient</th>
                        @foreach($stickyProducts as $stickyProduct)
                            <th>{{ $stickyProduct->name }}</th>
                        @endforeach
                        <th>Total</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                    <tr role="row" class="filter">
                        <td></td>
                        <td>{!! Form::text('filter[reference]', null, ['class' => 'form-control form-filter input-sm']) !!}</td>
                        <td>
                            {!! Form::text('filter[checkout_at][from]', null, ['class' => 'form-control form-filter input-sm date-picker', 'placeholder' => 'From (YYYY-MM-DD)', 'data-date-format' => 'yyyy-mm-dd']) !!}
                            {!! Form::text('filter[checkout_at][to]', null, ['class' => 'form-control form-filter input-sm date-picker', 'placeholder' => 'To (YYYY-MM-DD)', 'data-date-format' => 'yyyy-mm-dd']) !!}
                        </td>
                        @if(config('project.enable_delivery_date', FALSE))
                        <td>
                            {!! Form::text('filter[delivery_date][from]', null, ['class' => 'form-control form-filter input-sm date-picker', 'placeholder' => 'From (YYYY-MM-DD)', 'data-date-format' => 'yyyy-mm-dd']) !!}
                            {!! Form::text('filter[delivery_date][to]', null, ['class' => 'form-control form-filter input-sm date-picker', 'placeholder' => 'To (YYYY-MM-DD)', 'data-date-format' => 'yyyy-mm-dd']) !!}
                        </td>
                        @endif
                        <td>{!! Form::text('filter[billing]', null, ['class' => 'form-control form-filter input-sm']) !!}</td>
                        <td>{!! Form::text('filter[shipping]', null, ['class' => 'form-control form-filter input-sm']) !!}</td>
                        @foreach($stickyProducts as $stickyProduct)
                            <td></td>
                        @endforeach
                        <td></td>
                        <td>{!! Form::select('filter[status]', ['' => 'All'] + \Kommercio\Models\Order\Order::getStatusOptions(), null, ['class' => 'form-control form-filter input-sm']) !!}</td>
                        <td>
                            <div class="margin-bottom-5 btn-group btn-group-xs">
                                <button class="btn btn-default filter-submit margin-bottom">
                                    <i class="fa fa-search"></i> Search</button>
                                <button class="btn btn-default filter-cancel">
                                    <i class="fa fa-times"></i> Reset</button>
                            </div>
                        </td>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@stop