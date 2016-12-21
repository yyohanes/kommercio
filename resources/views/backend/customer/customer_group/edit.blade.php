@extends('backend.master.form_template')

@section('breadcrumb')
    <li>
        <a href="{{ route('backend.customer.index') }}"><span>Customer</span></a>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <a href="{{ route('backend.customer.group.index') }}"><span>Customer Group</span></a>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Edit Customer Group - {{ $customerGroup->name }}</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        {!! Form::model($customerGroup, ['route' => ['backend.customer.group.update', 'id' => $customerGroup->id], 'class' => 'form-horizontal']) !!}
        <div class="portlet light portlet-fit portlet-form bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase"> Edit {{ $customerGroup->name }} </span>
                </div>
                <div class="actions">
                    <button class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Save </button>
                    <button class="btn btn-link btn-sm" href="{{ NavigationHelper::getBackUrl() }}"><i class="fa fa-remove"></i> Cancel </button>
                </div>
            </div>

            <div class="portlet-body">
                <div class="form-body">
                    @include('backend.customer.customer_group.create_form')
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