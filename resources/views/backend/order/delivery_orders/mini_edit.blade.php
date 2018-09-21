<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
    <h4 class="modal-title">Delivery Order #{{ $deliveryOrder->reference }}</h4>
</div>

{!! Form::open(['route' => ['backend.sales.order.delivery_order.mini_save', 'id' => $deliveryOrder->id], 'class' => 'form-horizontal']) !!}
<div class="modal-body">
    <div class="form-body">
        @include('backend.master.form.fields.textarea', [
            'name' => 'notes',
            'label' => 'Notes',
            'key' => 'notes',
            'attr' => [
                'class' => 'form-control',
                'id' => 'notes',
                'rows' => 4,
                'placeholder' => 'Notes'
            ],
            'defaultValue' => $deliveryOrder->notes,
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
            'defaultValue' => $deliveryOrder->getData('tracking_number'),
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
            'defaultValue' => $deliveryOrder->getData('delivered_by'),
        ])

        <div class="clearfix"></div>
    </div>
</div>

<div class="modal-footer text-center">
    <div class="pull-right">
        <button class="btn btn-primary"><i class="fa fa-check"></i> Save </button>
        <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-remove"></i> Cancel</button>
        {!! Form::hidden('backUrl', $backUrl) !!}
    </div>
</div>
{!! Form::close() !!}
