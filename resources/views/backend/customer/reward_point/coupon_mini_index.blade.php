<div class="table-scrollable">
    <table class="table table-hover">
        <thead>
        <tr>
            <th> No </th>
            <th> Reward </th>
            <th> Points </th>
            <th> Redeemed at </th>
            <th> Status </th>
            <th>  </th>
        </tr>
        </thead>
        <tbody>
        @foreach($redemptions as $idx => $redemption)
            <tr>
                <td>
                    {{ $idx + 1 }}
                </td>
                <td>
                    {{ $redemption->reward->name }} <code>{{ $redemption->coupon->coupon_code }}</code>
                </td>
                <td>
                    {{ $redemption->points + 0 }}
                </td>
                <td>
                    {{ $redemption->created_at->format('d M Y, H:i') }}
                </td>
                <td>
                    @if($redemption->coupon->getRewardUsageCount() < 1)
                        <span class="label label-info">Unused</span>
                    @else
                        <span class="label label-warning">Used</span>
                    @endif
                </td>
                <td>
                    @if(Gate::allows('access', ['mark_used_redemptions']) && $redemption->coupon->getRewardUsageCount() < 1 && $redemption->coupon->type == \Kommercio\Models\PriceRule\Coupon::TYPE_OFFLINE)
                        {!! Form::open(['route' => ['backend.customer.redemption.mark_used', 'id' => $redemption->id], 'class' => 'form-in-btn-group']) !!}
                        <button class="btn btn-xs btn-default" data-toggle="confirmation" data-original-title="Are you sure?"><i class="fa fa-check"></i> Mark as Used</button>
                        {!! Form::close() !!}
                    @endif

                    @if($redemption->coupon->getRewardUsageCount() > 0)
                        Used at: <span class="label label-sm label-default">{{ $redemption->coupon->rewardUsageLogs->first()->created_at->format('d M Y H:i') }}</span>
                    @endif
                </td>
            </tr>
@endforeach
        </tbody>
    </table>
</div>