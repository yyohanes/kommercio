@foreach($payments as $payment)
    <tr>
        <td> {{ $payment->created_at->format('D, d M Y') }} </td>
        <td> {{ PriceFormatter::formatNumber($payment->amount, $payment->currency) }} </td>
        <td> <span class="label bg-{{ OrderHelper::getPaymentStatusLabelClass($payment->status) }} bg-font-{{ OrderHelper::getPaymentStatusLabelClass($payment->status) }}">{{ \Kommercio\Models\Order\Payment::getStatusOptions($payment->status) }}</span> </td>
        <td>{!! nl2br($payment->notes) !!}</td>
        <td>
            @if($payment->createdBy)
                {{ $payment->createdBy->fullName }}
            @else
                Customer
            @endif
        </td>
        <td style="width: 20%;">
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