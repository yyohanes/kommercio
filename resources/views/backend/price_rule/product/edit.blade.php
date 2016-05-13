@extends('backend.master.form_template')

@section('breadcrumb')
    <li>
        <span>Catalog</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <a href="{{ route('backend.price_rule.product.index') }}"><span>Price Rules</span></a>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Edit {{ $priceRule->name }} - Product Price Rule</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        {!! Form::model($priceRule, ['route' => ['backend.price_rule.product.update', 'id' => $priceRule->id], 'class' => 'form-horizontal']) !!}
        <div class="portlet light portlet-fit portlet-form bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase"> Edit {{ $priceRule->name }} - Product Price Rule </span>
                </div>
                <div class="actions">
                    <button class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Save </button>
                    <button class="btn btn-link btn-sm" href="{{ NavigationHelper::getBackUrl() }}"><i class="fa fa-remove"></i> Cancel </button>
                </div>
            </div>

            <div class="portlet-body">
                <div class="form-body">
                    @include('backend.price_rule.product.create_form')
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