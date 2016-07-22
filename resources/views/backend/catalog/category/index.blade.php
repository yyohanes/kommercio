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
        <span>Category</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase"> {{ $parentCategory?$parentCategory->name:'Categories' }} </span>
                </div>
                <div class="actions">
                    @can('create_product_category')
                    <a href="{{ route('backend.catalog.category.create', ['parent_id' => $parentCategory?$parentCategory->id:null, 'backUrl' => Request::fullUrl()]) }}" class="btn btn-sm btn-info">
                        <i class="fa fa-plus"></i> Add </a>
                    @endcan
                </div>
            </div>

            <div class="portlet-body">
                <table class="table table-striped table-bordered table-advance dataset-reorder" id="categories-dataset" data-form_token="{{ csrf_token() }}" data-row_class="category-name" data-row_value="category_id"\ data-reorder_action="{{ route('backend.catalog.category.reorder') }}">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th style="width: 10%;">Subcategories</th>
                        <th style="width: 10%;">Active</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($categories as $category)
                        <tr>
                            <td>
                                @can('edit_product_category')
                                <i class="fa fa-reorder"></i>
                                @endcan
                                <a class="category-name btn btn-sm blue-madison" data-category_id="{{ $category->id }}" href="{{ route('backend.catalog.category.index', ['parent' => $category->id]) }}">{{ $category->name }} (ID: {{ $category->id }})</a></td>
                            <td>{!! $category->description !!}</td>
                            <td>{{ $category->childrenCount }}</td>
                            <td><i class="fa fa-{{ $category->active?'check text-success':'remove text-danger' }}"></i></td>
                            <td class="text-center">
                                {!! Form::open(['route' => ['backend.catalog.category.delete', 'id' => $category->id]]) !!}
                                <div class="btn-group btn-group-sm">
                                    @can('edit_product_category')
                                    <a class="btn btn-default" href="{{ route('backend.catalog.category.edit', ['id' => $category->id, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-pencil"></i> Edit</a>
                                    @endcan
                                    @can('delete_product_category')
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

        @if($parentCategory)
            <a href="{{ route('backend.catalog.category.index', ['parent' => $parentCategory->parent_id]) }}" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
        @endif
    </div>
@stop