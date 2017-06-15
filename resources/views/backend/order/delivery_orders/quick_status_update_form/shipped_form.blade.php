<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
    <h4 class="modal-title">Mark Deliver Order as Shipped</h4>
</div>

{!! Form::model($deliveryOrder, ['route' => ['backend.sales.order.delivery_order.quick_status_update', 'status' => \Kommercio\Models\Order\DeliveryOrder\DeliveryOrder::STATUS_SHIPPED, 'id' => $deliveryOrder->id], 'id' => 'form-cancel-'.$deliveryOrder->id]) !!}
<div class="modal-body">
    <div class="form-body">
        <div class="note note-info">
            <p>You may update the input Tracking Number or other relevant informations.</p>
        </div>

        @include('backend.master.form.fields.textarea', [
            'name' => 'notes',
            'label' => 'Internal Notes',
            'key' => 'notes',
            'attr' => [
                'class' => 'form-control',
                'id' => 'notes',
                'rows' => 4,
                'placeholder' => 'Notes'
            ],
        ])

        <div class="clearfix"></div>

        <hr/>

        @include('backend.master.form.fields.text', [
            'name' => 'tracking_number',
            'label' => 'Tracking Number (if any)',
            'key' => 'tracking_number',
            'attr' => [
                'class' => 'form-control',
                'id' => 'tracking_number',
            ],
            'defaultValue' => $deliveryOrder->getData('tracking_number')
        ])

        <div class="clearfix"></div>

        @include('backend.master.form.fields.text', [
            'name' => 'delivered_by',
            'label' => 'Delivered by (if any)',
            'key' => 'delivered_by',
            'attr' => [
                'class' => 'form-control',
                'id' => 'delivered_by',
            ],
            'defaultValue' => $deliveryOrder->getData('delivered_by')
        ])

        <div class="clearfix"></div>
    </div>
</div>
<div class="modal-footer text-center">
    <div class="pull-left">
        <div class="checkbox-list text-left">
            <label class="checkbox">
                {!! Form::checkbox('send_notification', 1, true) !!} Send email notification to customer
            </label>
        </div>
    </div>

    <div class="pull-right">
        <button class="btn btn-primary"><i class="fa fa-check"></i> Confirm </button>
        <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-remove"></i> Cancel</button>
        {!! Form::hidden('backUrl', $backUrl.'#tab_delivery_orders') !!}
    </div>
</div>
{!! Form::close() !!}