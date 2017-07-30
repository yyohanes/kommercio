@extends('backend.master.form_template')

@section('breadcrumb')
    <li>
        <span>Configuration</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Address</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Import All Address</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        {!! Form::open(['route' => ['backend.configuration.address.import_all', 'type' => 'country'], 'class' => 'form-horizontal']) !!}
        <div class="portlet light portlet-fit portlet-form bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase"> Import All Address </span>
                </div>
            </div>

            <div class="portlet-body">
                <div class="form-body">
                    @include('backend.master.form.fields.text', [
                        'name' => 'import_url',
                        'label' => 'Import URL',
                        'key' => 'import_url',
                        'attr' => [
                            'class' => 'form-control',
                            'id' => 'import_url',
                        ],
                        'required' => true,
                    ])
                </div>

                <div class="form-body">
                    <div class="row">
                        <div class="col-md-3"> </div>
                        <div class="col-md-9">
                            <label>
                                {!! Form::checkbox('state', 1, true) !!}
                                Include State
                            </label>

                            <label>
                                {!! Form::checkbox('city', 1, true) !!}
                                Include City
                            </label>

                            <label>
                                {!! Form::checkbox('district', 1, true) !!}
                                Include District
                            </label>

                            <label>
                                {!! Form::checkbox('area', 1, true) !!}
                                Include Area
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-actions text-center">
                    <button class="btn btn-primary"><i class="fa fa-save"></i> Import </button>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
@stop