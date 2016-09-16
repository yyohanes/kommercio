<hr/>

@include('backend.master.form.fields.text', [
    'name' => 'data[secret_key]',
    'label' => 'Secret Key',
    'key' => 'data.secret_key',
    'attr' => [
        'class' => 'form-control',
        'id' => 'data[secret_key]'
    ],
    'defaultValue' => old('data.secret_key', $paymentMethod->getData('secret_key')),
    'required' => TRUE
])

@include('backend.master.form.fields.text', [
    'name' => 'data[publishable_key]',
    'label' => 'Publishable Key',
    'key' => 'data.publishable_key',
    'attr' => [
        'class' => 'form-control',
        'id' => 'data[publishable_key]'
    ],
    'defaultValue' => old('data.publishable_key', $paymentMethod->getData('publishable_key')),
    'required' => TRUE
])