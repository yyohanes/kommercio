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
        <a href="{{ route('backend.catalog.product_configuration.group.index') }}"><span>Configuration</span></a>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>{{ $productConfigurationGroup->name }} - Configuration</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase"> {{ $productConfigurationGroup->name }} - Configuration </span>
                </div>
                <div class="actions">
                    <a href="{{ route('backend.catalog.product_configuration.create', ['group_id' => $productConfigurationGroup->id, 'backUrl' => Request::fullUrl()]) }}" class="btn btn-sm btn-info">
                        <i class="fa fa-plus"></i> Add </a>
                </div>
            </div>

            <div class="portlet-body">
                <table class="table table-striped table-bordered table-advance dataset-reorder" id="product-configurations-dataset" data-form_token="{{ csrf_token() }}" data-row_class="configuration-name" data-row_value="configuration_id" data-reorder_action="{{ route('backend.catalog.product_configuration.reorder', ['group_id' => $productConfigurationGroup->id]) }}">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Required</th>
                        <th style="width: 20%;">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($configurations as $configuration)
                        <tr>
                            <td><i class="fa fa-reorder"></i> <span class="configuration-name" data-configuration_id="{{ $configuration->id }}">{{ $configuration->name }}</span></td>
                            <td>
                                {{ \Kommercio\Models\Product\Configuration\ProductConfiguration::getTypeOptions($configuration->type) }}
                            </td>
                            <td>
                                <i class="fa fa-{{ $configuration->pivot->required?'check text-success':'remove text-danger' }}"></i></td>
                            </td>
                            <td class="text-center">
                                {!! Form::open(['route' => ['backend.catalog.product_configuration.delete', 'group_id' => $productConfigurationGroup->id, 'id' => $configuration->id]]) !!}
                                <div class="btn-group btn-group-sm">
                                    <a class="btn btn-default" href="{{ route('backend.catalog.product_configuration.edit', ['group_id' => $productConfigurationGroup->id, 'id' => $configuration->id, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-pencil"></i> Edit</a>
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

        <a href="{{ route('backend.catalog.product_configuration.group.index') }}" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
    </div>
@stop