<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
    <h4 class="modal-title">Cancel Deliver Order</h4>
</div>

{!! Form::open(['route' => ['backend.sales.order.delivery_order.quick_status_update', 'status' => \Kommercio\Models\Order\DeliveryOrder\DeliveryOrder::STATUS_CANCELLED, 'id' => $deliveryOrder->id], 'class' => 'form-client-validation', 'id' => 'form-cancel-'.$deliveryOrder->id]) !!}
<div class="modal-body">
    <div class="form-body">
        @include('backend.master.form.fields.textarea', [
            'name' => 'cancel_notes',
            'label' => 'Why do you cancel this?',
            'key' => 'cancel_notes',
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
    <div class="pull-right">
        <button class="btn btn-primary"><i class="fa fa-check"></i> Confirm </button>
        <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-remove"></i> Cancel</button>
        {!! Form::hidden('backUrl', $backUrl.'#tab_delivery_orders') !!}
    </div>
</div>
{!! Form::close() !!}