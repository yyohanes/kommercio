<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
    <h4 class="modal-title">Resend {{ ucfirst($process) }} Email</h4>
</div>

{!! Form::open(['class' => 'form-client-validation', 'route' => ['backend.sales.order.resend_email', 'process' => $process, 'id' => $order->id]]) !!}
<div class="modal-body">
    <div class="form-body">
        @include('backend.master.form.fields.email', [
            'name' => 'email',
            'label' => 'Resend To',
            'key' => 'email',
            'attr' => [
                'class' => 'form-control',
                'id' => 'email',
                'data-rule-required' => 'true',
                'data-rule-email' => 'true',
                'placeholder' => $order->billingInformation->email,
            ],
            'defaultValue' => $order->billingInformation->email,
        ])

        <div class="clearfix"></div>

        <div class="form-group">
            <p>You are about to resend {{ ucfirst($process) }} Email. Do you want to continue?</p>
        </div>
    </div>
</div>

<div class="modal-footer text-center">
    <button class="btn btn-primary"><i class="fa fa-check"></i> Confirm </button>
    <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-remove"></i> Cancel</button>
    {!! Form::hidden('backUrl', $backUrl) !!}
</div>
{!! Form::close() !!}