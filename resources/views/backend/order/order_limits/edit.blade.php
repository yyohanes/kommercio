@extends('backend.master.form_template')

@section('breadcrumb')
    <li>
        <span>Sales</span>
        <i class="fa fa-circle"></i>
    </li>

    <li>
        <a href="{{ route('backend.order_limit.index', ['type' => $type]) }}"><span>{{ \Kommercio\Models\Order\OrderLimit::getTypeOptions($type) }} Order Limit</span></a>
        <i class="fa fa-circle"></i>
    </li>

    <li>
        <span>Edit {{ \Kommercio\Models\Order\OrderLimit::getTypeOptions($orderLimit->type) }} Order Limit</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        {!! Form::model($orderLimit, ['route' => ['backend.order_limit.update', 'id' => $orderLimit->id, 'backUrl' => NavigationHelper::getBackUrl()], 'class' => 'form-horizontal']) !!}
        <div class="portlet light portlet-fit portlet-form bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase">Edit {{ \Kommercio\Models\Order\OrderLimit::getTypeOptions($orderLimit->type) }} Order Limit</span>
                </div>
                <div class="actions">
                    <button class="btn btn-primary btn-sm"><i class="fa fa-save"></i> Save </button>
                    <button class="btn btn-link btn-sm" href="{{ NavigationHelper::getBackUrl() }}"><i class="fa fa-remove"></i> Cancel </button>
                </div>
            </div>

            <div class="portlet-body">
                <div class="form-body">
                    @include('backend.order.order_limits.create_form')
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