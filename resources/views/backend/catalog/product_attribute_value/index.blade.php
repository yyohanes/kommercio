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
        <a href="{{ route('backend.catalog.product_attribute.index') }}"><span>Product Attribute</span></a>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>{{ $productAttribute->name }}</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase"> {{ $productAttribute->name }}</span>
                </div>
                <div class="actions">
                    <a href="{{ route('backend.catalog.product_attribute.value.create', ['attribute_id' => $productAttribute->id, 'backUrl' => Request::fullUrl()]) }}" class="btn btn-sm btn-info">
                        <i class="fa fa-plus"></i> Add </a>
                </div>
            </div>

            <div class="portlet-body">
                <table class="table table-striped table-bordered table-advance dataset-reorder" id="product-attributes-dataset" data-form_token="{{ csrf_token() }}" data-row_class="attribute-name" data-row_value="attribute_id" data-reorder_action="{{ route('backend.catalog.product_attribute.value.reorder', ['attribute_id' => $productAttribute->id]) }}">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th style="width: 20%;">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($productAttributeValues as $productAttributeValue)
                        <tr>
                            <td><i class="fa fa-reorder"></i> <span class="attribute-name" data-attribute_id="{{ $productAttributeValue->id }}">{{ $productAttributeValue->name }}</span></td>
                            <td class="text-center">
                                {!! Form::open(['route' => ['backend.catalog.product_attribute.value.delete', 'attribute_id' => $productAttribute->id, 'id' => $productAttributeValue->id]]) !!}
                                <div class="btn-group btn-group-sm">
                                    <a class="btn btn-default" href="{{ route('backend.catalog.product_attribute.value.edit', ['attribute_id' => $productAttribute->id, 'id' => $productAttributeValue->id, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-pencil"></i> Edit</a>
                                    <button class="btn btn-default" data-toggle="confirmation" data-original-title="Are you sure?" title=""><i class="fa fa-trash-o"></i> Delete</button>
                                </div>
                                {!! Form::close() !!}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <a href="{{ route('backend.catalog.product_attribute.index') }}" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
    </div>
@stop