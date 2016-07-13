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
        <span>{{ $address->name }} Shipping Rates</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-truck"></i> {{ $address->name }} Shipping Rates</div>
                <div class="actions btn-set">
                    <button class="btn btn-link btn-sm" href="{{ NavigationHelper::getBackUrl() }}"><i class="fa fa-arrow-left"></i> Back </button>
                </div>
            </div>
            <div class="portlet-body">
                <div class="tabbable-bordered">
                    <ul class="nav nav-tabs" role="tablist">
                        @foreach($settingableShippingMethods as $idx => $settingableShippingMethod)
                        <li class="{{ $idx==0?'active':'' }}" role="presentation">
                            <a href="#tab_{{ $idx }}" data-toggle="tab"> {{ $settingableShippingMethod->name }} </a>
                        </li>
                        @endforeach
                    </ul>
                    <div class="tab-content">
                        @foreach($settingableShippingMethods as $idx => $settingableShippingMethod)
                        <div class="tab-pane {{ $idx==0?'active':'' }}" id="tab_{{ $idx }}">
                            {!! $settingableShippingMethod->getProcessor()->renderSettingView($address) !!}
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop