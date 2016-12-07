@extends('backend.master.layout')

@section('top_page_styles')
<link href="{{ asset('backend/assets/template/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('backend/assets/template/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css') }}" rel="stylesheet" type="text/css" />
@stop

@section('bottom_page_scripts')
    <script src="{{ asset('backend/assets/template/global/scripts/datatable.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js') }}" type="text/javascript"></script>

    <script src="{{ asset('backend/assets/scripts/pages/table_reorder.js') }}" type="text/javascript"></script>
@stop

@section('breadcrumb')
    <li>
        <a href="{{ route('backend.customer.index') }}"><span>Customer</span></a>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Reward Rules</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase">Reward Rules</span>
                </div>
                <div class="actions">
                    @can('access', ['create_reward_points_rules'])
                    <a href="{{ route('backend.customer.reward_rule.create', ['backUrl' => Request::fullUrl()]) }}" class="btn btn-sm btn-info">
                        <i class="fa fa-plus"></i> Add </a>
                    @endcan
                </div>
            </div>

            <div class="portlet-body">
                <table class="table table-striped table-bordered table-advance dataset-reorder" id="reward-rules-dataset" data-form_token="{{ csrf_token() }}" data-row_class="reward-rule-name" data-row_value="reward_rule_id" data-reorder_action="{{ route('backend.customer.reward_rule.reorder') }}">
                    <thead>
                    <tr>
                        <th style="width: 10px;"></th>
                        <th> Name </th>
                        <th> Type </th>
                        <th> Rule </th>
                        <th> Reward </th>
                        <th> Member Only </th>
                        <th> Currency </th>
                        <th> Store </th>
                        <th> Active </th>
                        <th style="width: 20%;"> Actions </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($rewardRules as $rewardRule)
                        <?php
                        $canStoreManage = Gate::allows('manage_store', [$rewardRule]);
                        ?>
                        <tr>
                            <td><i class="fa fa-reorder"></i></td>
                            <td class="reward-rule-name" data-reward_rule_id="{{ $rewardRule->id }}">{{ $rewardRule->name }}</td>
                            <td> {{ \Kommercio\Models\RewardPoint\RewardRule::getTypeOptions($rewardRule->type) }} </td>
                            <td> @include('backend.customer.reward_rule.rule.print.'.$rewardRule->type) </td>
                            <td> {{ $rewardRule->reward + 0 }} </td>
                            <td> <i class="fa {{ $rewardRule->member?'fa-check text-success':'fa-remove text-danger' }}"></i> </td>
                            <td> {{ $rewardRule->currency?CurrencyHelper::getCurrency($rewardRule->currency)['iso']:'All' }} </td>
                            <td> {{ $rewardRule->store?$rewardRule->store->name:'All' }} </td>
                            <td> <i class="fa {{ $rewardRule->active?'fa-check text-success':'fa-remove text-danger' }}"></i> </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    {!! Form::open(['route' => ['backend.customer.reward_rule.delete', 'id' => $rewardRule->id]]) !!}
                                    <div class="btn-group btn-group-sm">
                                        @if(Gate::allows('access', ['edit_reward_points_rule']) && $canStoreManage)
                                        <a class="btn btn-default" href="{{ route('backend.customer.reward_rule.edit', ['id' => $rewardRule->id, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-pencil"></i> Edit</a>
                                        @endif
                                        @if(Gate::allows('access', ['delete_reward_points_rule']) && $canStoreManage)
                                        <button class="btn btn-default" data-toggle="confirmation" data-original-title="Are you sure?" title=""><i class="fa fa-trash-o"></i> Delete</button>
                                        @endif
                                    </div>
                                    {!! Form::close() !!}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop