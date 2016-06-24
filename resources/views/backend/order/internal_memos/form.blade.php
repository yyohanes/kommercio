<div class="portlet light bordered">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject">@if($orderComment->exists) Edit Internal Memo @else New Internal Memo @endif</span>
        </div>
    </div>

    <div class="portlet-body">
        {!! Form::open(['route' => ['backend.sales.order.internal_memo.save', 'order_id' => $order->id], 'class' => 'form-horizontal']) !!}
        @include('backend.master.form.fields.textarea', [
            'name' => 'internal_memo[body]',
            'label' => 'Memo',
            'key' => 'internal_memo.body',
            'attr' => [
                'class' => 'form-control',
                'id' => 'internal_memo[body]',
                'rows' => 3
            ],
        ])

        <div class="margin-top-15 text-center">
            <button id="internal-memo-save" data-internal_memo_save="{{ route('backend.sales.order.internal_memo.save', ['order_id' => $order->id]) }}" class="btn btn-info"><i class="fa fa-save"></i> Submit Memo</button>
            <button id="internal-memo-cancel" class="btn btn-default"><i class="fa fa-remove"></i> Cancel</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>