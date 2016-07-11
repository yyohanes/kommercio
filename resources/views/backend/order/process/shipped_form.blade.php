<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
    <h4 class="modal-title">Ship Order</h4>
</div>

{!! Form::open(['route' => ['backend.sales.order.process', 'process' => 'shipped', 'id' => $order->id]]) !!}
<div class="modal-body">
    <div class="form-body">
        @include('backend.master.form.fields.text', [
            'name' => 'tracking_number',
            'label' => 'Tracking Number (if any)',
            'key' => 'tracking_number',
            'attr' => [
                'class' => 'form-control',
                'id' => 'tracking_number',
                'data-rule-required' => 'true'
            ]
        ])

        <div class="clearfix"></div>

        <div class="form-group" style="margin-top: 1em;">
            <div class="checkbox-list">
                <label class="checkbox-inline">
                    {!! Form::checkbox('send_notification', 1, true) !!} Send email notification to customer
                </label>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer text-center">
    <button class="btn btn-primary"><i class="fa fa-check"></i> Confirm </button>
    <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-remove"></i> Cancel</button>
    {!! Form::hidden('backUrl', $backUrl) !!}
</div>
{!! Form::close() !!}