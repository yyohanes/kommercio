<hr/>

@include('backend.master.form.fields.text', [
    'name' => 'data[api_key]',
    'label' => 'API Key',
    'key' => 'data.api_key',
    'attr' => [
        'class' => 'form-control',
        'id' => 'data[api_key]'
    ],
    'defaultValue' => old('data.api_key', $paymentMethod->getData('api_key')),
    'required' => TRUE
])

<div class="form-group">
    <label class="col-md-3">&nbsp;</label>
    <div class="col-md-9">
        Please don't forget to include <strong>Stripe.js</strong> in frontend.
    </div>
</div>