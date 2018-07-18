@extends('backend.master.layout')

@section('top_page_styles')
<link href="{{ asset('backend/assets/template/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('backend/assets/template/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css') }}" rel="stylesheet" type="text/css" />
@stop

@section('bottom_page_scripts')
    <script src="{{ asset('backend/assets/template/global/scripts/datatable.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js') }}" type="text/javascript"></script>

    <script>
        enable_customer_group = {{ ProjectHelper::isFeatureEnabled('customer.customer_group')?'true':'false' }};
    </script>
    <script src="{{ asset('backend/assets/scripts/pages/customer_index.js?cb=1') }}" type="text/javascript"></script>
@stop

@section('breadcrumb')
    <li>
        <span>Customer</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase"> Customers </span>
                </div>
                <div class="actions">
                    @can('access', ['create_customer'])
                    <a href="{{ route('backend.customer.create', ['backUrl' => Request::fullUrl()]) }}" class="btn btn-sm btn-info">
                        <i class="fa fa-plus"></i> Add </a>

                    @if(ProjectHelper::isFeatureEnabled('customer.export'))
                    <a href="{{ route('backend.utility.export.customer', Request::except('external_filter') + ['backUrl' => Request::fullUrl()]) }}" class="btn btn-sm btn-default">
                        <i class="fa fa-file-o"></i> Export </a>
                    @endif
                    @endcan
                </div>
            </div>

            <div class="portlet-body">
                <table class="table table-striped table-bordered table-advance" id="customers-dataset" data-src="{{ Request::fullUrl() }}" data-form_token="{{ csrf_token() }}">
                    <thead>
                    <tr role="row" class="heading">
                        <th style="width: 10px;"></th>
                        <th>Salute</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Account</th>
                        <th>Status</th>
                        @if(ProjectHelper::isFeatureEnabled('customer.customer_group'))
                        <th>Group</th>
                        @endif
                        <th>Since</th>
                        <th>Last Seen</th>
                        <th>Order Count</th>
                        <th>Order Total</th>
                        <th></th>
                    </tr>
                    <tr role="row" class="filter" id="customer-filter-form" data-customer_index="{{ route('backend.customer.index') }}">
                        <td></td>
                        <td>{!! Form::select('filter[salute]', ['' => 'All'] + \Kommercio\Models\Customer::getSaluteOptions(), null, ['class' => 'form-control form-filter input-sm']) !!}</td>
                        <td>{!! Form::text('filter[full_name]', null, ['class' => 'form-control form-filter input-sm']) !!}</td>
                        <td>{!! Form::text('filter[email]', null, ['class' => 'form-control form-filter input-sm']) !!}</td>
                        <td>{!! Form::select('filter[account]', ['' => 'All'] + ['1' => 'Has Account', '0' => 'No Account'], null, ['class' => 'form-control form-filter input-sm']) !!}</td>
                        <td>{!! Form::select('filter[status]', ['' => 'All'] + \Kommercio\Models\User::getStatusOptions(), null, ['class' => 'form-control form-filter input-sm']) !!}</td>
                        @if(ProjectHelper::isFeatureEnabled('customer.customer_group'))
                        <td>{!! Form::select('filter[customer_group]', ['' => 'All'] + \Kommercio\Models\Customer\CustomerGroup::getCustomerGroupOptions(), null, ['class' => 'form-control form-filter input-sm']) !!}</td>
                        @endif
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>
                            <div class="margin-bottom-5 btn-group btn-group-xs">
                                <button id="customer-filter-btn" class="btn btn-default margin-bottom">
                                    <i class="fa fa-search"></i> Search</button>
                                <a href="{{ route('backend.customer.index') }}" class="btn btn-default">
                                    <i class="fa fa-times"></i> Reset</a>
                            </div>
                        </td>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@stop
