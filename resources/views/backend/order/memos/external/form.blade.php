<div class="portlet light bordered">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject">@if($orderComment->exists) Edit External Memo @else New External Memo @endif</span>
        </div>
    </div>

    <div class="portlet-body">
        {!! Form::open(['route' => ['backend.sales.order.external_memo.save', 'order_id' => $order->id, 'id' => $orderComment->id], 'class' => 'form-horizontal']) !!}
        @include('backend.master.form.fields.textarea', [
            'name' => 'external_memo[body]',
            'label' => 'Memo',
            'key' => 'external_memo.body',
            'attr' => [
                'class' => 'form-control',
                'id' => 'external_memo[body]',
                'rows' => 3
            ],
            'defaultValue' => $orderComment->body
        ])

        <div class="margin-top-15 text-center">
            <button id="external-memo-save" data-external_memo_save="{{ route('backend.sales.order.external_memo.save', ['order_id' => $order->id, 'id' => $orderComment->id]) }}" class="btn btn-info"><i class="fa fa-save"></i> Save Memo</button>
            <button id="external-memo-cancel" class="btn btn-default"><i class="fa fa-remove"></i> Cancel</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>