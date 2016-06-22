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
        <span>Configuration</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <a href="{{ route('backend.tax.index') }}"><span>Tax</span></a>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase"> Tax </span>
                </div>
                <div class="actions">
                    @can('access', ['create_tax'])
                    <a href="{{ route('backend.tax.create', ['backUrl' => Request::fullUrl()]) }}" class="btn btn-sm btn-info">
                        <i class="fa fa-plus"></i> Add </a>
                    @endcan
                </div>
            </div>

            <div class="portlet-body">
                <table class="table table-striped table-bordered table-advance dataset-reorder" id="taxes-dataset" data-form_token="{{ csrf_token() }}" data-row_class="tax-name" data-row_value="tax_id" data-reorder_action="{{ route('backend.tax.reorder') }}">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Rate</th>
                        <th>Active</th>
                        <th>Currency</th>
                        <th>Store</th>
                        <th>Country</th>
                        <th style="width: 20%;">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($taxes as $tax)
                        <tr>
                            <td><i class="fa fa-reorder"></i> <span class="tax-name" data-tax_id="{{ $tax->id }}">{{ $tax->name }}</span></td>
                            <td>{{ $tax->rate }}%</td>
                            <td><i class="fa fa-{{ $tax->active?'check text-success':'remove text-danger' }}"></i></td>
                            <td> {{ $tax->currency?CurrencyHelper::getCurrency($tax->currency)['iso']:'All' }} </td>
                            <td> {{ $tax->store?$tax->store->name:'All' }} </td>
                            <td>{{ $tax->countries->get(0)?$tax->countries->get(0)->name:'All Countries' }}</td>
                            <td class="text-center">
                                {!! Form::open(['route' => ['backend.tax.delete', 'id' => $tax->id]]) !!}
                                <div class="btn-group btn-group-sm">
                                    @can('access', ['edit_tax'])
                                    <a class="btn btn-default" href="{{ route('backend.tax.edit', ['id' => $tax->id, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-pencil"></i> Edit</a>
                                    @endcan

                                    @can('access', ['delete_tax'])
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