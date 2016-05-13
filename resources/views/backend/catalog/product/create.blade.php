@extends('backend.master.form_template')

@section('breadcrumb')
    <li>
        <span>Catalog</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <a href="{{ route('backend.catalog.product.index') }}"><span>Product</span></a>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Create Product</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        {!! Form::model($product, ['route' => 'backend.catalog.product.store', 'class' => 'form form-horizontal form-row-seperated']) !!}
            <div class="portlet">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-shopping-cart"></i>Create Product </div>
                    <div class="actions btn-set">
                        <button name="action" value="save_stay" class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Save & Stay </button>
                        <button name="action" value="save" class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Save </button>
                        <button class="btn btn-link btn-sm" href="{{ NavigationHelper::getBackUrl() }}"><i class="fa fa-arrow-left"></i> Back </button>
                    </div>
                </div>
                <div class="portlet-body">
                    @include('backend.catalog.product.create_form')

                    <div class="form-actions text-center">
                        <button name="action" value="save_stay" class="btn btn-primary"><i class="fa fa-save"></i> Save & Stay </button>
                        <button name="action" value="save" class="btn btn-primary"><i class="fa fa-save"></i> Save </button>
                        <button class="btn btn-link" href="{{ NavigationHelper::getBackUrl() }}"><i class="fa fa-arrow-left"></i> Back </button>
                    </div>
                </div>
            </div>
        {!! Form::close() !!}
    </div>
@stop