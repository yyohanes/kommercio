@extends('backend.master.layout')

@section('top_page_styles')
<link href="{{ asset('backend/assets/template/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('backend/assets/template/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css') }}" rel="stylesheet" type="text/css" />
@stop

@section('bottom_page_scripts')
    <script src="{{ asset('backend/assets/template/global/scripts/datatable.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js') }}" type="text/javascript"></script>

    <script src="{{ asset('backend/assets/scripts/pages/product_index.js') }}" type="text/javascript"></script>
@stop

@section('breadcrumb')
    <li>
        <span>Catalog</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Product</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase"> Products </span>
                </div>
                <div class="actions">
                    <a href="{{ route('backend.catalog.product.create', ['backUrl' => Request::fullUrl()]) }}" class="btn btn-sm btn-info">
                        <i class="fa fa-plus"></i> Add </a>
                </div>
            </div>

            <div class="portlet-body">
                <table class="table table-striped table-bordered table-advance" id="products-dataset" data-src="{{ route('backend.catalog.product.index') }}" data-form_token="{{ csrf_token() }}">
                    <thead>
                    <tr role="row" class="heading">
                        <th style="width: 10px;"></th>
                        <th class="product-image">Image</th>
                        <th>Name</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Manufacturer</th>
                        <th>Price</th>
                        <th>Net Price</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                    <tr role="row" class="filter">
                        <td></td>
                        <td></td>
                        <td>{!! Form::text('filter[name]', null, ['class' => 'form-control form-filter input-sm']) !!}</td>
                        <td>{!! Form::text('filter[sku]', null, ['class' => 'form-control form-filter input-sm']) !!}</td>
                        <td>{!! Form::text('filter[category_name]', null, ['class' => 'form-control form-filter input-sm']) !!}</td>
                        <td>{!! Form::text('filter[manufacturer]', null, ['class' => 'form-control form-filter input-sm']) !!}</td>
                        <td></td>
                        <td></td>
                        <td>{!! Form::select('filter[active]', ['' => 'All', '1' => 'Active', '0' => 'Inactive'], null, ['class' => 'form-control form-filter input-sm']) !!}</td>
                        <td></td>
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