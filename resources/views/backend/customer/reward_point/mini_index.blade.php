<div class="table-scrollable">
    <table class="table table-hover">
        <thead>
        <tr>
            <th> No </th>
            <th> Amount </th>
            <th> Reason </th>
            <th> Type </th>
            <th> Status </th>
            <th> By </th>
            <th> Notes </th>
            <th></th>
        </tr>
        </thead>
        <tbody>
@foreach($rewardPointTransactions as $idx => $rewardPointTransaction)
            <tr>
                <td>
                    {{ $idx + 1 }}
                </td>
                <td>
                    {{ ProjectHelper::formatNumber($rewardPointTransaction->amount) }}
                </td>
                <td>
                    {!! nl2br($rewardPointTransaction->reason) !!}
                </td>
                <td>
                    <span class="label label-{{ $rewardPointTransaction->type == \Kommercio\Models\RewardPoint\RewardPointTransaction::TYPE_ADD?'info':'warning' }}">{{ \Kommercio\Models\RewardPoint\RewardPointTransaction::getTypeOptions($rewardPointTransaction->type) }}</span>
                </td>
                <td>
                    @if($rewardPointTransaction->status == \Kommercio\Models\RewardPoint\RewardPointTransaction::STATUS_APPROVED)
                        <span class="text-success"><i class="fa fa-check"></i></span>
                    @elseif($rewardPointTransaction->status == \Kommercio\Models\RewardPoint\RewardPointTransaction::STATUS_DECLINED)
                        <span class="text-danger"><i class="fa fa-remove"></i></span>
                    @else
                        <i class="fa fa-clock-o"></i>
                    @endif
                </td>
                <td>
                    {{ $rewardPointTransaction->createdBy->fullName }}
                </td>
                <td>
                    {!! nl2br($rewardPointTransaction->notes) !!}
                </td>
                <td>
                    @if(Gate::allows('access', ['approve_reward_points']) && $rewardPointTransaction->status == \Kommercio\Models\RewardPoint\RewardPointTransaction::STATUS_REVIEW)
                        {!! Form::open(['route' => ['backend.customer.reward_point.approve', 'id' => $rewardPointTransaction->id], 'class' => 'form-in-btn-group']) !!}
                        <button class="btn btn-xs btn-default" data-toggle="confirmation" data-original-title="Are you sure?"><i class="fa fa-check"></i> Approve</button>
                        {!! Form::close() !!}
                    @endif
                    @if(Gate::allows('access', ['reject_reward_points']) && $rewardPointTransaction->status == \Kommercio\Models\RewardPoint\RewardPointTransaction::STATUS_REVIEW)
                        {!! Form::open(['route' => ['backend.customer.reward_point.reject', 'id' => $rewardPointTransaction->id], 'class' => 'form-in-btn-group']) !!}
                        <button class="btn btn-xs btn-default" data-toggle="confirmation" data-original-title="Are you sure?"><i class="fa fa-remove"></i> Reject</button>
                        {!! Form::close() !!}
                    @endif
                </td>
            </tr>
@endforeach
        </tbody>
    </table>
</div>