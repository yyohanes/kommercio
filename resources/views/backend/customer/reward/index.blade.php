@extends('backend.master.layout')

@section('top_page_styles')
<link href="{{ asset('backend/assets/template/global/plugins/datatables/datatables.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('backend/assets/template/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css') }}" rel="stylesheet" type="text/css" />
@stop

@section('bottom_page_scripts')
    <script src="{{ asset('backend/assets/template/global/scripts/datatable.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/datatables/datatables.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/template/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js') }}" type="text/javascript"></script>
@stop

@section('breadcrumb')
    <li>
        <a href="{{ route('backend.customer.index') }}"><span>Customer</span></a>
        <i class="fa fa-circle"></i>
    </li>
    <li>
        <span>Rewards</span>
    </li>
@stop

@section('content')
    <div class="col-md-12">
        <div class="portlet light portlet-fit portlet-datatable bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject sbold uppercase">Rewards</span>
                </div>
                <div class="actions">
                    @can('access', ['create_reward'])
                    <a href="{{ route('backend.customer.reward.create', ['backUrl' => Request::fullUrl()]) }}" class="btn btn-sm btn-info">
                        <i class="fa fa-plus"></i> Add </a>
                    @endcan
                </div>
            </div>

            <div class="portlet-body">
                <table class="table table-striped table-bordered table-advance" id="reward-dataset">
                    <thead>
                    <tr>
                        <th style="width: 10px;"></th>
                        <th> Name </th>
                        <th> Description </th>
                        <th> Points </th>
                        <th> Type </th>
                        <th> Store </th>
                        <th> Active </th>
                        <th style="width: 20%;"> Actions </th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($rewards as $reward)
                        <?php
                        $canStoreManage = Gate::allows('manage_store', [$reward]);
                        ?>
                        <tr>
                            <td><i class="fa fa-reorder"></i></td>
                            <td>{{ $reward->name }}</td>
                            <td>{!! $reward->description !!}</td>
                            <td> {{ $reward->points + 0 }} </td>
                            <td> {{ \Kommercio\Models\RewardPoint\Reward::getTypeOptions($reward->type) }} </td>
                            <td> {{ $reward->store?$reward->store->name:'All' }} </td>
                            <td> <i class="fa {{ $reward->active?'fa-check text-success':'fa-remove text-danger' }}"></i> </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    {!! Form::open(['route' => ['backend.customer.reward.delete', 'id' => $reward->id]]) !!}
                                    <div class="btn-group btn-group-sm">
                                        @if(Gate::allows('access', ['edit_reward']) && $canStoreManage)
                                        <a class="btn btn-default" href="{{ route('backend.customer.reward.edit', ['id' => $reward->id, 'backUrl' => Request::fullUrl()]) }}"><i class="fa fa-pencil"></i> Edit</a>
                                        @endif
                                        @if(Gate::allows('access', ['delete_reward']) && $canStoreManage)
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