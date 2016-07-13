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
        <span>Import {{ ucfirst($address->addressType) }}</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        {!! Form::model($address, ['route' => ['backend.configuration.address.import', 'type' => $type, 'id' => $address->id], 'files' => TRUE, 'class' => 'form-horizontal']) !!}
        <div class="portlet light portlet-fit portlet-form bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase"> Import {{ ucfirst($address->addressType) }} </span>
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

                <div class="form-actions text-center">
                    <button class="btn btn-primary"><i class="fa fa-save"></i> Import </button>
                    <button class="btn btn-link" href="{{ NavigationHelper::getBackUrl() }}"><i class="fa fa-remove"></i> Cancel </button>
                </div>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
@stop