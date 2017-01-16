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
        <a href="{{ route('backend.catalog.product_composite.group.index') }}"><span>Composite</span></a>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>{{ $productCompositeGroup->name }} - Composite</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase"> {{ $productCompositeGroup->name }} - Composite </span>
                </div>
                <div class="actions">
                    <a href="{{ route('backend.catalog.product_composite.create', ['group_id' => $productCompositeGroup->id, 'backUrl' => Request::fullUrl()]) }}" class="btn btn-sm btn-info">
                        <i class="fa fa-plus"></i> Add </a>
                </div>
            </div>

            <br class="portlet-body">
                <table class="table table-striped table-bordered table-advance dataset-reorder" id="product-composites-dataset" data-form_token="{{ csrf_token() }}" data-row_class="composite-name" data-row_value="composite_id" data-reorder_action="{{ route('backend.catalog.product_composite.reorder', ['group_id' => $productCompositeGroup->id]) }}">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Include</th>
                        <th>Minimum</th>
                        <th>Maximum</th>
                        <th style="width: 20%;">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($composites as $composite)
                        <tr>
                            <td><i class="fa fa-reorder"></i> <span class="composite-name" data-composite_id="{{ $composite->id }}">{{ $composite->name }}</span></td>
                            <td>
                                @if($composite->products->count() > 0)
                                    <div><strong>Product</strong></br>
                                    @foreach($composite->products as $product)
                                        - {{ $product->name }}</br>
                                    @endforeach
                                    </div>
                                    </br>
                                @endif

                                @if($composite->productCategories->count() > 0)
                                    <div><strong>Product Category</strong></br>
                                    @foreach($composite->productCategories as $productCategory)
                                        - {{ $productCategory->name }}</br>
                                    @endforeach
                                    </div>
                                @endif
                            </td>
                            <td>
                                {{ $composite->minimum + 0 }}
                            </td>
                            <td>
                                {{ $composite->maximum + 0 }}
                            </td>
                            <td class="text-center">
                                {!! Form::open(['route' => ['backend.catalog.product_composite.delete', 'group_id' => $productCompositeGroup->id, 'id' => $composite->id]]) !!}
                                <div class="btn-group btn-group-sm">
                                    <a class="btn btn-default" href="{{ route('backend.catalog.product_composite.edit', ['group_id' => $productCompositeGroup->id, 'id' => $composite->id, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-pencil"></i> Edit</a>
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

        <a href="{{ route('backend.catalog.product_composite.group.index') }}" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
    </div>
@stop