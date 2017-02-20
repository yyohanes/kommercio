@extends('backend.master.form_template')

@section('breadcrumb')
    <li>
        <span>Configuration</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Utility</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Import</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Product</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        {!! Form::open(['route' => ['backend.utility.import.product'], 'files' => TRUE, 'class' => 'form-horizontal']) !!}
        <div class="portlet light portlet-fit portlet-form bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase"> Import Product </span>
                </div>

                <div class="actions">
                    <a href="{{ asset('backend/assets/import-samples/sample_product.xlsx') }}" class="btn btn-sm btn-warning">
                        <i class="fa fa-file-excel-o"></i> Download Sample Format</a>
                </div>
            </div>

            <div class="portlet-body">
                <div class="form-body">
                    @include('backend.master.form.fields.file', [
                        'name' => 'file',
                        'label' => 'Excel',
                        'key' => 'file',
                        'attr' => [
                            'class' => 'form-control',
                            'id' => 'file',
                        ],
                        'help_text' => 'Format must strictly follow our sample.',
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