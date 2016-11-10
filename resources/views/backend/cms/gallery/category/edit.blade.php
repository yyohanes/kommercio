@extends('backend.master.form_template')

@section('breadcrumb')
    <li>
        <span>CMS</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <a href="{{ route('backend.cms.gallery.index') }}"><span>Gallery</span></a>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <a href="{{ route('backend.cms.gallery.category.index') }}"><span>Gallery Category</span></a>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Edit {{ $galleryCategory->name }}</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        {!! Form::model($galleryCategory, ['route' => ['backend.cms.gallery.category.update', 'id' => $galleryCategory->id], 'files' => TRUE, 'class' => 'form-horizontal files-upload-form']) !!}
        <div class="portlet light portlet-fit portlet-form bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase"> Edit {{ $galleryCategory->name }} </span>
                </div>
                <div class="actions">
                    <button class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Save </button>
                    <button class="btn btn-link btn-sm" href="{{ NavigationHelper::getBackUrl() }}"><i class="fa fa-remove"></i> Cancel </button>
                </div>
            </div>

            <div class="portlet-body">
                <div class="form-body">
                    @include('backend.cms.gallery.category.create_form')
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