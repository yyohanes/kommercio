<table class="table table-hover table-bordered table-striped quick-content-table">
    <tbody>
    @foreach($payments as $payment)
        <tr>
            <td>
                <span class="pull-right badge bg-{{ OrderHelper::getPaymentStatusLabelClass($payment->status) }} bg-font-{{ OrderHelper::getPaymentStatusLabelClass($payment->status) }}">{{ \Kommercio\Models\Order\Payment::getStatusOptions($payment->status) }}</span>
                <p>
                    <i class="fa fa-clock-o"></i> {{ $payment->payment_date?$payment->payment_date->format('D, d M Y'):null }}<br/>
                    <i class="fa fa-money"></i> {{ PriceFormatter::formatNumber($payment->amount, $payment->currency) }}<br/>
                    @foreach($payment->attachments as $attachment)
                        <a href="{{ asset($attachment->getImagePath('enlarge')) }}" class="fancybox-button" rel="attachments-{{ $attachment->id }}"><i class="fa fa-paperclip"></i> {{ $attachment->name }}</a><br/>
                    @endforeach
                </p>

                <br/>
                <ul class="list-group">
                    <li class="list-group-item">
                        Payment entered by {{ $payment->createdBy?$payment->createdBy->email:'Customer' }}<br/>
                        @if($payment->notes)
                            Notes:<br/>{!! nl2br($payment->notes) !!}
                        @endif

                        <div>
                        <span class="badge badge-default">{{ $payment->created_at->format('d-m-Y H:i') }}</span>
                        </div>
                    </li>
                    @foreach($payment->getHistory() as $history)
                        <li class="list-group-item">
                            Payment set to {{ \Kommercio\Models\Order\Payment::getStatusOptions($history->value) }} by {{ $history->author }}<br/>
                            @if($history->notes)
                                <pre>Reason: {!! nl2br($history->notes) !!}</pre>
                            @endif
                            <div><span class="badge badge-default">{{ $payment->created_at->format('d-m-Y H:i') }}</span></div>
                        </li>
                    @endforeach
                </ul>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>