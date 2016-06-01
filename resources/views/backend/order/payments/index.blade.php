@foreach($payments as $payment)
    <tr>
        <td> {{ $payment->created_at->format('D, d M Y') }} </td>
        <td> {{ PriceFormatter::formatNumber($payment->amount, $payment->currency) }} </td>
        <td> <span class="label bg-{{ OrderHelper::getPaymentStatusLabelClass($payment->status) }} bg-font-{{ OrderHelper::getPaymentStatusLabelClass($payment->status) }}">{{ \Kommercio\Models\Order\Payment::getStatusOptions($payment->status) }}</span> </td>
        <td>
            <ul class="list-group">
                <li class="list-group-item">
                    Payment entered by {{ $payment->createdBy?$payment->createdBy->fullName:Customer }}<br/>
                    Notes: {!! nl2br($payment->notes) !!}
                    <span class="badge badge-default">{{ $payment->created_at->format('d-m-Y H:i') }}</span>
                </li>
                @foreach($payment->getHistory() as $history)
                    <li class="list-group-item">
                        Payment set to {{ $history['status'] }} by {{ $history['by'] }}<br/>
                        Reason: {!! nl2br($history['notes']) !!}
                        <span class="badge badge-default">{{ \Carbon\Carbon::parse($history['at'])->format('d-m-Y H:i') }}</span>
                    </li>
                @endforeach
            </ul>
        </td>
        <td>
            <div class="btn-group btn-group-sm">
                @if(in_array($payment->status, [\Kommercio\Models\Order\Payment::STATUS_REVIEW]))
                    <a href="{{ route('backend.sales.order.payment.process', ['id' => $payment->id, 'process' => 'accept', 'backUrl' => route('backend.sales.order.view', ['order_id' => $payment->order_id]).'#tab_payments']) }}" class="modal-ajax btn btn-default">Accept</a>
                @endif

                @if(!in_array($payment->status, [\Kommercio\Models\Order\Payment::STATUS_VOID]))
                <a href="{{ route('backend.sales.order.payment.process', ['id' => $payment->id, 'process' => 'void', 'backUrl' => route('backend.sales.order.view', ['order_id' => $payment->order_id]).'#tab_payments']) }}" class="modal-ajax btn btn-default">Void</a>
                @endif
            </div>
        </td>
    </tr>
@endforeach