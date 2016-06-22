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
        <span>Catalog</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Product Attribute</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase"> Product Attributes </span>
                </div>
                <div class="actions">
                    @can('access', ['create_product_attribute'])
                    <a href="{{ route('backend.catalog.product_attribute.create', ['backUrl' => Request::fullUrl()]) }}" class="btn btn-sm btn-info">
                        <i class="fa fa-plus"></i> Add </a>
                    @endcan
                </div>
            </div>

            <div class="portlet-body">
                <table class="table table-striped table-bordered table-advance dataset-reorder" id="product-attributes-dataset" data-form_token="{{ csrf_token() }}" data-row_class="attribute-name" data-row_value="attribute_id" data-reorder_action="{{ route('backend.catalog.product_attribute.reorder') }}">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th style="width: 10%;">Values</th>
                        <th style="width: 20%;">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($productAttributes as $productAttribute)
                        <tr>
                            <td><i class="fa fa-reorder"></i> <a class="attribute-name btn btn-sm blue-madison" data-attribute_id="{{ $productAttribute->id }}" href="{{ route('backend.catalog.product_attribute.value.index', ['attribute_id' => $productAttribute]) }}">{{ $productAttribute->name }}</a></td>
                            <td>{{ $productAttribute->valueCount }}</td>
                            <td class="text-center">
                                {!! Form::open(['route' => ['backend.catalog.product_attribute.delete', 'id' => $productAttribute->id]]) !!}
                                <div class="btn-group btn-group-sm">
                                    @can('access', ['edit_product_attribute'])
                                    <a class="btn btn-default" href="{{ route('backend.catalog.product_attribute.edit', ['id' => $productAttribute->id, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-pencil"></i> Edit</a>
                                    @endcan
                                    @can('access', ['delete_product_attribute'])
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