@extends('backend.utility.import.master')

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

@section('form')
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

            <div class="form-body">
                @include('backend.master.form.fields.checkbox', [
                    'name' => 'import[override_existing]',
                    'label' => 'Override Existing',
                    'key' => 'import.override_existing',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => 'import[override_existing]',
                    ],
                    'value' => true,
                    'checked' => old('import.override_existing'),
                    'help_text' => 'Override product if it already exists',
                ])
            </div>

            <div class="form-body">
                @include('backend.master.form.fields.checkbox', [
                    'name' => 'import[redownload_images]',
                    'label' => 'Redownload Images',
                    'key' => 'import.redownload_images',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => 'import[redownload_images]',
                    ],
                    'value' => true,
                    'checked' => old('import.redownload_images'),
                    'help_text' => 'Redownload Images for existing product',
                ])
            </div>

            <div class="form-actions text-center">
                <button class="btn btn-primary"><i class="fa fa-save"></i> Import </button>
                <button class="btn btn-link" href="{{ NavigationHelper::getBackUrl() }}"><i class="fa fa-remove"></i> Cancel </button>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@endsection