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
        <span>Manufacturer</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase"> Manufacturers </span>
                </div>
                <div class="actions">
                    <a href="{{ route('backend.catalog.manufacturer.create', ['backUrl' => Request::fullUrl()]) }}" class="btn btn-sm btn-info">
                        <i class="fa fa-plus"></i> Add </a>
                </div>
            </div>

            <div class="portlet-body">
                <table class="table table-striped table-bordered table-advance dataset-table">
                    <thead>
                    <tr>
                        <th style="width: 10px;">Logo</th>
                        <th>Name</th>
                        <th>Product Count</th>
                        <th style="width: 20%;">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($manufacturers as $manufacturer)
                        <tr>
                            <td>@if($manufacturer->logo)<img style="width: 100px; height: auto;" src={{ asset($manufacturer->logo->getImagePath('small_logo')) }} />@endif</td>
                            <td>{{ $manufacturer->name }}</td>
                            <td>{{ $manufacturer->productCount }}</td>
                            <td class="text-center">
                                {!! Form::open(['route' => ['backend.catalog.manufacturer.delete', 'id' => $manufacturer->id]]) !!}
                                <div class="btn-group btn-group-sm">
                                    <a class="btn btn-default" href="{{ route('backend.catalog.manufacturer.edit', ['id' => $manufacturer->id, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-pencil"></i> Edit</a>
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
    </div>
@stop