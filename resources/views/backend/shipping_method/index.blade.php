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
        <span>Shipping Method</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase"> Shipping Method </span>
                </div>
                <div class="actions">
                    @can('access', ['create_shipping_method'])
                    <a href="{{ route('backend.shipping_method.create', ['backUrl' => Request::fullUrl()]) }}" class="btn btn-sm btn-info">
                        <i class="fa fa-plus"></i> Add </a>
                    @endcan
                </div>
            </div>

            <div class="portlet-body">
                <table class="table table-striped table-bordered table-advance dataset-reorder" id="shipping-methods-dataset" data-form_token="{{ csrf_token() }}" data-row_class="shipping-name" data-row_value="shipping_id" data-reorder_action="{{ route('backend.shipping_method.reorder') }}">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Stores</th>
                        <th style="width: 20%;">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($shippingMethods as $shippingMethod)
                        <tr>
                            <td><i class="fa fa-reorder"></i> <span class="shipping-name" data-shipping_id="{{ $shippingMethod->id }}">{{ $shippingMethod->name }}</span></td>
                            <td>{!! $shippingMethod->stores->count() > 0?$shippingMethod->stores->map(function($store){ return '<span class="label label-success">'.$store->name.'</span>'; })->implode(' '):'<span class="label label-default">All Stores</span>' !!}</td>
                            <td class="text-center">
                                {!! Form::open(['route' => ['backend.shipping_method.delete', 'id' => $shippingMethod->id]]) !!}
                                <div class="btn-group btn-group-sm">
                                    @can('access', ['edit_shipping_method'])
                                    <a class="btn btn-default" href="{{ route('backend.shipping_method.edit', ['id' => $shippingMethod->id, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-pencil"></i> Edit</a>
                                    @endcan

                                    @can('access', ['delete_shipping_method'])
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