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
        var show_store_column = {{ Auth::user()->manageMultipleStores?1:0 }};
        var view_payment = {{ Gate::allows('access', ['view_payment'])?1:0 }};
        var enable_delivery_date = {{ config('project.enable_delivery_date', false)?1:0 }};
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
                <div class="table-container">
                    <div class="table-actions-wrapper">
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
                    <table class="table table-striped table-bordered table-advance" id="orders-dataset" data-src="{{ Request::fullUrl() }}" data-form_token="{{ csrf_token() }}">
                        <thead>
                        <tr role="row" class="heading">
                            <th style="width: 10px;">
                                <input type="checkbox" class="group-checkable" />
                            </th>
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
                            @can('access', ['view_payment'])
                            <th>Payment Method</th>
                            <th>Outstanding</th>
                            @endcan
                            <th>Status</th>
                            @if(Auth::user()->manageMultipleStores)
                                <th>Store</th>
                            @endif
                            <th></th>
                        </tr>
                        <tr role="row" class="filter" id="order-filter-form" data-order_index="{{ route('backend.sales.order.index') }}">
                            <td></td>
                            <td></td>
                            <td>{!! Form::text('filter[reference]', Request::input('filter.reference'), ['class' => 'form-control form-filter input-sm']) !!}</td>
                            <td>
                                {!! Form::text('filter[checkout_at][from]', Request::input('filter.checkout_at.from'), ['class' => 'form-control form-filter input-sm date-picker', 'placeholder' => 'From (YYYY-MM-DD)', 'data-date-format' => 'yyyy-mm-dd']) !!}
                                {!! Form::text('filter[checkout_at][to]', Request::input('filter.checkout_at.to'), ['class' => 'form-control form-filter input-sm date-picker', 'placeholder' => 'To (YYYY-MM-DD)', 'data-date-format' => 'yyyy-mm-dd']) !!}
                            </td>
                            @if(config('project.enable_delivery_date', FALSE))
                                <td>
                                    {!! Form::text('filter[delivery_date][from]', Request::input('filter.delivery_date.from'), ['class' => 'form-control form-filter input-sm date-picker', 'placeholder' => 'From (YYYY-MM-DD)', 'data-date-format' => 'yyyy-mm-dd']) !!}
                                    {!! Form::text('filter[delivery_date][to]', Request::input('filter.delivery_date.to'), ['class' => 'form-control form-filter input-sm date-picker', 'placeholder' => 'To (YYYY-MM-DD)', 'data-date-format' => 'yyyy-mm-dd']) !!}
                                </td>
                            @endif
                            <td>{!! Form::text('filter[billing]', Request::input('filter.billing'), ['class' => 'form-control form-filter input-sm']) !!}</td>
                            <td>{!! Form::text('filter[shipping]', Request::input('filter.shipping'), ['class' => 'form-control form-filter input-sm']) !!}</td>
                            @foreach($stickyProducts as $stickyProduct)
                                <td></td>
                            @endforeach
                            <td></td>
                            @can('access', ['view_payment'])
                            <td>{!! Form::select('filter[payment_method_id]', ['' => 'All'] + $paymentMethodOptions, [Request::input('filter.payment_method_id')], ['class' => 'form-control form-filter input-sm']) !!}</td>
                            <td>{!! Form::select('filter[outstanding]', ['' => 'All', 'settled' => 'Settled', 'unsettled' => 'Unsettled'], [Request::input('filter.outstanding')], ['class' => 'form-control form-filter input-sm']) !!}</td>
                            @endcan
                            <td>{!! Form::select('filter[status]', ['' => 'All'] + \Kommercio\Models\Order\Order::getStatusOptions(), Request::input('filter.status'), ['class' => 'form-control form-filter input-sm']) !!}</td>
                            @if(Auth::user()->manageMultipleStores)
                                <td>{!! Form::select('filter[store_id]', ['' => 'All'] + \Kommercio\Models\Store::getStoreOptions(), Request::input('filter.store_id'), ['class' => 'form-control form-filter input-sm']) !!}</td>
                            @endif
                            <td>
                                <div class="margin-bottom-5 btn-group btn-group-xs">
                                    <button id="order-filter-btn" class="btn btn-default margin-bottom">
                                        <i class="fa fa-search"></i> Search</button>
                                    <a href="{{ route('backend.sales.order.index') }}" class="btn btn-default">
                                        <i class="fa fa-times"></i> Reset</a>
                                </div>
                            </td>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop