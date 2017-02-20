@extends('backend.master.form_template')

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
    <li>
        Edit {{ $configuration->name }} - {{ $productConfigurationGroup->name }} Configuration
    </li>
@stop

@section('content')
    <div class="col-md-12">
        {!! Form::model($configuration, ['route' => ['backend.catalog.product_configuration.update', 'group_id' => $productConfigurationGroup->id, 'id' => $configuration->id], 'class' => 'form-horizontal']) !!}
        <div class="portlet light portlet-fit portlet-form bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase"> Edit {{ $configuration->name }} - {{ $productConfigurationGroup->name }} Configuration </span>
                </div>
                <div class="actions">
                    <button class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Save </button>
                    <button class="btn btn-link btn-sm" href="{{ NavigationHelper::getBackUrl() }}"><i class="fa fa-remove"></i> Cancel </button>
                </div>
            </div>

            <div class="portlet-body">
                <div class="form-body">
                    @include('backend.catalog.product_configuration.create_form')
                </div>

                <div class="form-actions text-center">
                    <button class="btn btn-primary"><i class="fa fa-save"></i> Save </button>
                    <button class="btn btn-link" href="{{ NavigationHelper::getBackUrl() }}"><i class="fa fa-remove"></i> Cancel </button>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
@stop