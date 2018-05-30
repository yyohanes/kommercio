<hr/>

@include('backend.master.form.fields.select', [
    'name' => 'data[is_production]',
    'label' => 'Environment',
    'key' => 'data.is_production',
    'attr' => [
        'class' => 'form-control',
        'id' => 'data[is_production]'
    ],
    'options' => ['0' => 'Development', '1' => 'Production'],
    'defaultOptions' => old('data.is_production', $shippingMethod->getData('is_production')),
    'required' => TRUE
])

@include('backend.master.form.fields.text', [
    'name' => 'data[site_id]',
    'label' => 'Site ID',
    'key' => 'data.site_id',
    'attr' => [
        'class' => 'form-control',
        'id' => 'data[site_id]'
    ],
    'defaultValue' => old('data.site_id', $shippingMethod->getData('site_id')),
    'required' => TRUE,
])

@include('backend.master.form.fields.password', [
    'name' => 'data[password]',
    'label' => 'Password',
    'key' => 'data.password',
    'attr' => [
        'class' => 'form-control',
        'id' => 'data[password]'
    ],
    'defaultValue' => old('data.password', $shippingMethod->getData('password')),
])

@include('backend.master.form.fields.text', [
    'name' => 'data[company_name]',
    'label' => 'Company Name',
    'key' => 'data.company_name',
    'attr' => [
        'class' => 'form-control',
        'id' => 'data[company_name]'
    ],
    'defaultValue' => old('data.company_name', $shippingMethod->getData('company_name')),
    'required' => TRUE,
])

@include('backend.master.form.fields.text', [
    'name' => 'data[contact_person]',
    'label' => 'Contact Person',
    'key' => 'data.contact_person',
    'attr' => [
        'class' => 'form-control',
        'id' => 'data[contact_person]'
    ],
    'defaultValue' => old('data.contact_person', $shippingMethod->getData('contact_person')),
    'required' => TRUE,
])

@include('backend.master.form.fields.text', [
    'name' => 'data[contact_number]',
    'label' => 'Contact Number',
    'key' => 'data.contact_number',
    'attr' => [
        'class' => 'form-control',
        'id' => 'data[contact_number]'
    ],
    'defaultValue' => old('data.contact_number', $shippingMethod->getData('contact_number')),
    'required' => TRUE,
])

@include('backend.master.form.fields.email', [
    'name' => 'data[contact_email]',
    'label' => 'Contact Email',
    'key' => 'data.contact_email',
    'attr' => [
        'class' => 'form-control',
        'id' => 'data[contact_email]'
    ],
    'defaultValue' => old('data.contact_email', $shippingMethod->getData('contact_email')),
])

@include('backend.master.form.fields.text', [
    'name' => 'data[shipper_account_number]',
    'label' => 'Shipper Account Number',
    'key' => 'data.shipper_account_number',
    'attr' => [
        'class' => 'form-control',
        'id' => 'data[shipper_account_number]'
    ],
    'defaultValue' => old('data.shipper_account_number', $shippingMethod->getData('shipper_account_number')),
    'required' => TRUE,
])
