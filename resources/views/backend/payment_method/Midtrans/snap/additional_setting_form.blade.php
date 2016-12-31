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

@include('backend.master.form.fields.select', [
    'name' => 'data[3ds]',
    'label' => 'Enable 3D-Secure',
    'key' => 'data.3ds',
    'attr' => [
        'class' => 'form-control',
        'id' => 'data[3ds]'
    ],
    'options' => [0 => 'Disable', 1 => 'Enable'],
    'defaultOptions' => old('data.3ds', [$paymentMethod->getData('3ds', true)]),
    'required' => TRUE
])

@include('backend.master.form.fields.text', [
    'name' => 'data[merchant_id]',
    'label' => 'Merchant ID',
    'key' => 'data.merchant_id',
    'attr' => [
        'class' => 'form-control',
        'id' => 'data[merchant_id]'
    ],
    'defaultValue' => old('data.merchant_id', $paymentMethod->getData('merchant_id')),
    'required' => TRUE
])

@include('backend.master.form.fields.text', [
    'name' => 'data[client_key]',
    'label' => 'Client Key',
    'key' => 'data.client_key',
    'attr' => [
        'class' => 'form-control',
        'id' => 'data[client_key]'
    ],
    'defaultValue' => old('data.client_key', $paymentMethod->getData('client_key')),
    'required' => TRUE
])

@include('backend.master.form.fields.text', [
    'name' => 'data[server_key]',
    'label' => 'Server Key',
    'key' => 'data.server_key',
    'attr' => [
        'class' => 'form-control',
        'id' => 'data[server_key]'
    ],
    'defaultValue' => old('data.server_key', $paymentMethod->getData('server_key')),
    'required' => TRUE
])