@foreach($externalMemos as $externalMemo)
    <tr>
        <td>
            {!! nl2br($externalMemo->body) !!}
            @if($externalMemo->getData('key') == 'cancelled')
                <div><em>Reason: {!! nl2br($externalMemo->getData('reason', '')) !!}</em></div>
            @endif
        </td>
        <td>{{ $externalMemo->getData('author_name') }}</td>
        <td><span class="badge badge-default">{{ $externalMemo->created_at->format('d-m-Y H:i') }}</span></td>
        <td>
            <div class="btn-group btn-group-sm">
                @can('access', ['edit_external_memo'])
                    <a class="external-memo-edit-btn btn btn-default" href="{{ route('backend.sales.order.external_memo.form', ['order_id' => $externalMemo->order_id, 'id' => $externalMemo->id]) }}"><i class="fa fa-pencil"></i> Edit</a>
                @endcan

                @can('access', ['delete_external_memo'])
                    <button
                            class="btn btn-default"
                            data-external_memo_delete="{{ route('backend.sales.order.external_memo.delete', ['order_id' => $externalMemo->order_id, 'id' => $externalMemo->id]) }}"
                            data-toggle="confirmation"
                            data-original-title="Are you sure?"
                            data-on-confirm="orderExternalMemoFormBehaviors.deleteExternalMemo"
                    >
                        <i class="fa fa-trash-o"></i> Delete
                    </button>
                @endcan
            </div>
        </td>
    </tr>
@endforeach