<hr/>

@include('backend.master.form.fields.select', [
    'name' => 'data[is_production]',
    'label' => 'Environment',
    'key' => 'data.is_production',
    'attr' => [
        'class' => 'form-control',
        'id' => 'data[is_production]'
    ],
    'options' => [0 => 'Development', 1 => 'Production'],
    'defaultOptions' => old('data.is_production', [$paymentMethod->getData('is_production')]),
    'required' => TRUE
])

@include('backend.master.form.fields.email', [
    'name' => 'data[email]',
    'label' => 'Paypal Email',
    'key' => 'data.email',
    'attr' => [
        'class' => 'form-control',
        'id' => 'data[email]'
    ],
    'defaultValue' => old('data.email', $paymentMethod->getData('email')),
    'required' => TRUE
])

@include('backend.master.form.fields.text', [
    'name' => 'data[client_id]',
    'label' => 'Client ID',
    'key' => 'data.client_id',
    'attr' => [
        'class' => 'form-control',
        'id' => 'data[client_id]'
    ],
    'defaultValue' => old('data.client_id', $paymentMethod->getData('client_id')),
    'required' => TRUE
])

@include('backend.master.form.fields.text', [
    'name' => 'data[secret_key]',
    'label' => 'Secret',
    'key' => 'data.secret_key',
    'attr' => [
        'class' => 'form-control',
        'id' => 'data[secret_key]'
    ],
    'defaultValue' => old('data.secret_key', $paymentMethod->getData('secret_key')),
    'required' => TRUE
])