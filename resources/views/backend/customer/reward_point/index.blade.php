@extends('backend.master.form_template')

@section('top_page_styles')
    @parent

<link href="{{ asset('backend/assets/template/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('backend/assets/template/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css') }}" rel="stylesheet" type="text/css" />
@stop

@section('bottom_page_scripts')
    @parent

    <script src="{{ asset('backend/assets/template/global/scripts/datatable.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js') }}" type="text/javascript"></script>

    <script src="{{ asset('backend/assets/scripts/pages/reward_point_index.js') }}" type="text/javascript"></script>
@stop

@section('breadcrumb')
    <li>
        <a href="{{ route('backend.customer.index') }}"><span>Customer</span></a>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Reward Point</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase"> Reward Points </span>
                </div>
                <div class="actions">

                </div>
            </div>

            <div class="portlet-body">
                <table class="table table-striped table-bordered table-advance" id="reward-points-dataset" data-src="{{ Request::fullUrl() }}" data-index="{{ route('backend.customer.reward_point.index') }}" data-form_token="{{ csrf_token() }}">
                    <thead>
                    <tr role="row" class="heading">
                        <th style="width: 10px;"></th>
                        <th> Customer </th>
                        <th> Amount </th>
                        <th> Reason </th>
                        <th> Type </th>
                        <th> Status </th>
                        <th> By </th>
                        <th> Notes </th>
                        <th> Time </th>
                        <th></th>
                    </tr>
                    <tr role="row" class="filter">
                        <td></td>
                        <td>{!! Form::text('filter[customer]', Request::input('filter.customer'), ['class' => 'form-control form-filter input-sm']) !!}</td>
                        <td></td>
                        <td></td>
                        <td>{!! Form::select('filter[type]', ['' => 'All'] + \Kommercio\Models\RewardPoint\RewardPointTransaction::getTypeOptions(), Request::input('filter.type'), ['class' => 'form-control form-filter input-sm']) !!}</td>
                        <td>{!! Form::select('filter[status]', ['' => 'All'] + \Kommercio\Models\RewardPoint\RewardPointTransaction::getStatusOptions(), Request::input('filter.status'), ['class' => 'form-control form-filter input-sm']) !!}</td>
                        <td></td>
                        <td></td>
                        <td>
                            {!! Form::text('filter[created_at][from]', Request::input('filter.created_at.from'), ['class' => 'form-control form-filter input-sm date-picker', 'placeholder' => 'From (YYYY-MM-DD)', 'data-date-format' => 'yyyy-mm-dd']) !!}
                            {!! Form::text('filter[created_at][to]', Request::input('filter.created_at.to'), ['class' => 'form-control form-filter input-sm date-picker', 'placeholder' => 'To (YYYY-MM-DD)', 'data-date-format' => 'yyyy-mm-dd']) !!}
                        </td>
                        <td>
                            <div class="margin-bottom-5 btn-group btn-group-xs">
                                <button class="btn btn-default btn-filter-submit margin-bottom">
                                    <i class="fa fa-search"></i> Search</button>
                                <a href="{{ route('backend.customer.reward_point.index') }}" class="btn btn-default">
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