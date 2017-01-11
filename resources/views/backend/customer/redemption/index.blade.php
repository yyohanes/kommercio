@extends('backend.master.layout')

@section('top_page_styles')
<link href="{{ asset('backend/assets/template/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('backend/assets/template/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css') }}" rel="stylesheet" type="text/css" />
@stop

@section('bottom_page_scripts')
    <script src="{{ asset('backend/assets/template/global/scripts/datatable.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js') }}" type="text/javascript"></script>

    <script src="{{ asset('backend/assets/scripts/pages/redemption_index.js') }}" type="text/javascript"></script>
@stop

@section('breadcrumb')
    <li>
        <a href="{{ route('backend.customer.index') }}"><span>Customer</span></a>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Redemption</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase"> Redemptions </span>
                </div>
                <div class="actions">

                </div>
            </div>

            <div class="portlet-body">
                <table class="table table-striped table-bordered table-advance" id="redemptions-dataset" data-src="{{ Request::fullUrl() }}" data-form_token="{{ csrf_token() }}">
                    <thead>
                    <tr role="row" class="heading">
                        <th style="width: 10px;"></th>
                        <th> Reward </th>
                        <th> Points </th>
                        <th> Customer </th>
                        <th> Redeemed at </th>
                        <th> Status </th>
                        <th>  </th>
                    </tr>
                    <tr role="row" class="filter" id="redemption-filter-form" data-redemption_index="{{ route('backend.customer.redemption.index') }}">
                        <td></td>
                        <td>{!! Form::text('filter[reward]', Request::input('filter.reward'), ['class' => 'form-control form-filter input-sm']) !!}</td>
                        <td></td>
                        <td>{!! Form::text('filter[customer]', Request::input('filter.customer'), ['class' => 'form-control form-filter input-sm']) !!}</td>
                        <td>
                            {!! Form::text('filter[created_at][from]', Request::input('filter.created_at.from'), ['class' => 'form-control form-filter input-sm date-picker', 'placeholder' => 'From (YYYY-MM-DD)', 'data-date-format' => 'yyyy-mm-dd']) !!}
                            {!! Form::text('filter[created_at][to]', Request::input('filter.created_at.to'), ['class' => 'form-control form-filter input-sm date-picker', 'placeholder' => 'To (YYYY-MM-DD)', 'data-date-format' => 'yyyy-mm-dd']) !!}
                        </td>
                        <td>{!! Form::select('filter[status]', ['' => 'All', 'used' => 'Redemeed', 'unused' => 'Unredeemed'], Request::input('filter.status'), ['class' => 'form-control form-filter input-sm']) !!}</td>
                        <td>
                            <div class="margin-bottom-5 btn-group btn-group-xs">
                                <button id="redemption-filter-btn" class="btn btn-default margin-bottom">
                                    <i class="fa fa-search"></i> Search</button>
                                <a href="{{ route('backend.customer.redemption.index') }}" class="btn btn-default">
                                    <i class="fa fa-times"></i> Reset</a>
                            </div>
                        </td>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@stop