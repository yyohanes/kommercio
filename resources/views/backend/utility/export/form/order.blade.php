@extends('backend.utility.export.master')

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
        <span>Export</span>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Order</span>
    </li>
@stop

@section('form')
    {!! Form::open(['route' => ['backend.utility.export.order', 'filter' => isset($filter)?$filter:[]], 'class' => 'form-horizontal']) !!}
    <div class="portlet light portlet-fit portlet-form bordered">
        <div class="portlet-title">
            <div class="caption">
                <span class="caption-subject sbold uppercase"> Export Order </span>
            </div>
        </div>

        <div class="portlet-body">
            <div class="form-body">
                <p>There are {{ $totalOrders.' '.str_plural('order', $totalOrders) }} to be exported.</p>
            </div>

            <div class="form-actions text-center">
                <button class="btn btn-primary"><i class="fa fa-save"></i> Export </button>
                <button class="btn btn-link" href="{{ NavigationHelper::getBackUrl() }}"><i class="fa fa-remove"></i> Cancel </button>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@stop
