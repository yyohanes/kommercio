<div class="row">
    @include('backend.master.form.fields.select', [
        'name' => 'payment_method',
        'label' => null,
        'key' => 'payment_method',
        'options' => $paymentMethodOptions,
        'defaultOptions' => $order->paymentMethod?$order->paymentMethod->id:null,
        'attr' => [
            'class' => 'form-control',
        ]
    ])
</div>

<div class="form-actions">
    <button class="btn btn-primary btn-xs"><i class="fa fa-check"></i> Save </button>
    {!! Form::hidden('backUrl', $backUrl) !!}
</div>