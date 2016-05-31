<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
    <h4 class="modal-title">Accept Payment</h4>
</div>

{!! Form::open(['route' => ['backend.sales.order.payment.process', 'process' => 'accept', 'id' => $payment->id]]) !!}
<div class="modal-body">
    <div class="form-body">
        <div class="form-group">
            @include('backend.master.form.fields.textarea', [
            'name' => 'reason',
            'label' => 'Accept Reason',
            'key' => 'reason',
            'attr' => [
                'class' => 'form-control',
                'id' => 'reason',
                'rows' => 3
            ],
        ])
        </div>

        <div class="clearfix"></div>
    </div>
</div>
<div class="modal-footer text-center">
    <button class="btn btn-primary"><i class="fa fa-check"></i> Confirm </button>
    <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-remove"></i> Cancel</button>
    {!! Form::hidden('backUrl', $backUrl) !!}
</div>
{!! Form::close() !!}