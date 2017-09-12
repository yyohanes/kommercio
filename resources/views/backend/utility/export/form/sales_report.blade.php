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
        <span>Sales Report</span>
    </li>
@stop

@section('form')
    {!! Form::open(['route' => ['backend.utility.export.sales_report', 'search' => $filter], 'class' => 'form-horizontal']) !!}
    <div class="portlet light portlet-fit portlet-form bordered">
        <div class="portlet-title">
            <div class="caption">
                <span class="caption-subject sbold uppercase"> Export Sales Report </span>
            </div>
        </div>

        <div class="portlet-body">
            <div class="form-body">
                @if(isset($filter['year']))
                    Year: {{ $filter['year'] }}<br/>
                    Status: {{ implode(', ', $filter['status']) }}<br/>
                    Store: {{ is_numeric($filter['store']) ? \Kommercio\Models\Store::findOrFail($filter['store'])->name : 'All Stores' }}
                @else
                    @php
                    $dateTypeOptions = ['checkout_at' => 'Order Date'];
                    if (ProjectHelper::getConfig('enable_delivery_date', false)) {
                        $dateTypeOptions['delivery_date'] = 'Delivery Date';
                    }
                    @endphp
                    @if($filter)
                        Type: {{ $dateTypeOptions[$filter['date_type']] }}<br/>
                        Date From: {{ $filter['date']['from'] }}<br/>
                        Date To: {{ $filter['date']['to'] }}<br/>
                        Status: {{ implode(', ', $filter['status']) }}<br/>
                        Store: {{ is_numeric($filter['store']) ? \Kommercio\Models\Store::findOrFail($filter['store'])->name : 'All Stores' }}
                    @endif
                @endif
            </div>

            <div class="form-actions text-center">
                <button class="btn btn-primary"><i class="fa fa-save"></i> Export </button>
                <button class="btn btn-link" href="{{ NavigationHelper::getBackUrl() }}"><i class="fa fa-remove"></i> Cancel </button>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@stop
