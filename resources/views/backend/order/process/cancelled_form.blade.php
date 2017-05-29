<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
    <h4 class="modal-title">Cancel Order</h4>
</div>

{!! Form::open(['route' => ['backend.sales.order.process', 'process' => 'cancelled', 'id' => $order->id], 'class' => 'form-client-validation', 'id' => 'form-cancel-'.$order->id]) !!}
<div class="modal-body">
    <div class="form-body">
        @include('backend.master.form.fields.textarea', [
            'name' => 'notes',
            'label' => 'Why do you cancel this?',
            'key' => 'notes',
            'attr' => [
                'class' => 'form-control',
                'id' => 'notes',
                'rows' => 3,
                'data-rule-required' => 'true'
            ]
        ])

        <div class="clearfix"></div>
    </div>
</div>
<div class="modal-footer text-center">
    <div class="pull-left">
        <div class="checkbox-list">
            <label class="checkbox-inline">
                {!! Form::checkbox('send_notification', 1, true) !!} Send email notification to customer
            </label>
        </div>
    </div>

    <div class="pull-right">
        <button class="btn btn-primary"><i class="fa fa-check"></i> Confirm </button>
        <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-remove"></i> Cancel</button>
        {!! Form::hidden('backUrl', $backUrl) !!}
    </div>
</div>
{!! Form::close() !!}