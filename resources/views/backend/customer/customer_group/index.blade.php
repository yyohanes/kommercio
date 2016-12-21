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
        <a href="{{ route('backend.customer.index') }}"><span>Customer</span></a>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Customer Group</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase"> Customer Group </span>
                </div>
                <div class="actions">
                    @can('access', ['create_customer_group'])
                    <a href="{{ route('backend.customer.group.create', ['backUrl' => Request::fullUrl()]) }}" class="btn btn-sm btn-info">
                        <i class="fa fa-plus"></i> Add </a>
                    @endcan
                </div>
            </div>

            <div class="portlet-body">
                <table class="table table-striped table-bordered table-advance dataset-reorder" id="customer-groups-dataset" data-form_token="{{ csrf_token() }}" data-row_class="customer-group-name" data-row_value="customer_group_id" data-reorder_action="{{ route('backend.customer.group.reorder') }}">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Customers</th>
                        <th style="width: 20%;">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($customerGroups as $customerGroup)
                        <tr>
                            <td><i class="fa fa-reorder"></i> <span class="customer-group-name" data-customer_group_id="{{ $customerGroup->id }}">{{ $customerGroup->name }}</span></td>
                            <td>{{ $customerGroup->customerCount }}</td>
                            <td class="text-center">
                                {!! Form::open(['route' => ['backend.customer.group.delete', 'id' => $customerGroup->id]]) !!}
                                <div class="btn-group btn-group-sm">
                                    @can('access', ['edit_customer_group'])
                                    <a class="btn btn-default" href="{{ route('backend.customer.group.edit', ['id' => $customerGroup->id, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-pencil"></i> Edit</a>
                                    @endcan

                                    @can('access', ['delete_customer_group'])
                                    <button class="btn btn-default" data-toggle="confirmation" data-original-title="Are you sure?" title=""><i class="fa fa-trash-o"></i> Delete</button>
                                    @endcan
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