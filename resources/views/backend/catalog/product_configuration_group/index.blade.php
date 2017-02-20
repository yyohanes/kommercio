@extends('backend.master.layout')

@section('top_page_styles')
<link href="{{ asset('backend/assets/template/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('backend/assets/template/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css') }}" rel="stylesheet" type="text/css" />
@stop

@section('bottom_page_scripts')
    <script src="{{ asset('backend/assets/template/global/scripts/datatable.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js') }}" type="text/javascript"></script>
@stop

@section('breadcrumb')
    <li>
        <span>Catalog</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Configuration</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase">Configuration</span>
                </div>
                <div class="actions">
                    @can('access', ['create_product_configuration_group'])
                    <a href="{{ route('backend.catalog.product_configuration.group.create', ['backUrl' => Request::fullUrl()]) }}" class="btn btn-sm btn-info">
                        <i class="fa fa-plus"></i> Add </a>
                    @endcan
                </div>
            </div>

            <div class="portlet-body">
                <table class="table table-striped table-bordered table-advance">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Configurations</th>
                        <th style="width: 20%;">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($productConfigurationGroups as $productConfigurationGroup)
                        <tr>
                            <td>
                                <a href="{{ route('backend.catalog.product_configuration.index', ['group_id' => $productConfigurationGroup->id]) }}" class="btn btn-sm btn-info">
                                    {{ $productConfigurationGroup->name }}
                                </a>
                            </td>
                            <td>
                                @foreach($productConfigurationGroup->configurations as $configuration)
                                    <div>- {{ $configuration->name }}</div>
                                @endforeach
                            </td>
                            <td class="text-center">
                                {!! Form::open(['route' => ['backend.catalog.product_configuration.group.delete', 'id' => $productConfigurationGroup->id]]) !!}
                                <div class="btn-group btn-group-sm">
                                    @can('access', ['edit_product_configuration_group'])
                                    <a class="btn btn-default" href="{{ route('backend.catalog.product_configuration.group.edit', ['id' => $productConfigurationGroup->id, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-pencil"></i> Edit</a>
                                    @endcan
                                    @can('access', ['delete_product_configuration_group'])
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