<div class="portlet light bordered">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject">@if($payment->exists) Edit Payment @else New Payment @endif</span>
        </div>
    </div>

    <div class="portlet-body">
        {!! Form::open(['route' => ['backend.sales.order.payment.save', 'order_id' => $order->id], 'class' => 'form-horizontal']) !!}
        @include('backend.master.form.fields.select', [
            'name' => 'payment[payment_method_id]',
            'label' => 'Payment Method',
            'key' => 'payment.payment_method_id',
            'attr' => [
                'class' => 'form-control',
                'id' => 'payment[payment_method_id]',
            ],
            'options' => $paymentMethodOptions,
        ])

        @include('backend.master.form.fields.select', [
            'name' => 'payment[currency]',
            'label' => 'Currency',
            'key' => 'payment.currency',
            'attr' => [
                'class' => 'form-control',
                'id' => 'payment[currency]',
            ],
            'options' => $currencyOptions,
        ])

        @include('backend.master.form.fields.number', [
            'name' => 'payment[amount]',
            'label' => 'Amount',
            'key' => 'payment.amount',
            'attr' => [
                'class' => 'form-control',
                'id' => 'payment[amount]',
                'data-currency_dependent' => '#payment\\[currency\\]',
                'data-number_type' => 'amount',
            ],
            'unit' => CurrencyHelper::getCurrentCurrency()['symbol'],
            'unitPosition' => 'front',
            'help_text' => 'Payment that has been entered can\'t be edited anymore.',
            'defaultValue' => $outstanding
        ])

        @include('backend.master.form.fields.textarea', [
            'name' => 'payment[notes]',
            'label' => 'Notes',
            'key' => 'payment.notes',
            'attr' => [
                'class' => 'form-control',
                'id' => 'payment[notes]',
                'rows' => 2
            ],
        ])

        @include('backend.master.form.fields.images', [
            'name' => 'attachments',
            'label' => 'Payment Proof',
            'key' => 'attachments',
            'attr' => [
                'class' => 'form-control',
                'id' => 'attachments'
            ],
            'multiple' => TRUE,
            'existing' => $payment->attachments,
            'caption' => FALSE
        ])

        <div class="margin-top-15 text-center">
            <button id="payment-save" data-payment_save="{{ route('backend.sales.order.payment.save', ['order_id' => $order->id]) }}" class="btn btn-info"><i class="fa fa-save"></i> Enter Payment</button>
            <button id="payment-cancel" class="btn btn-default"><i class="fa fa-remove"></i> Cancel</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>