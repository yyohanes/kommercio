<div class="customer-information-wrapper">
    @include('backend.master.form.fields.email', [
    'name' => $type.'[email]',
    'label' => 'Email',
    'key' => $type.'.email',
    'attr' => [
        'class' => 'form-control',
        'id' => $type.'[email]'
    ],
    'required' => TRUE,
])

    @include('backend.master.form.fields.text', [
        'name' => $type.'[full_name]',
        'label' => 'Full Name',
        'key' => $type.'.full_name',
        'attr' => [
            'class' => 'form-control',
            'id' => $type.'[full_name]'
        ],
        'required' => TRUE,
    ])

    @include('backend.master.form.fields.tel', [
        'name' => $type.'[phone_number]',
        'label' => 'Phone Number',
        'key' => $type.'.phone_number',
        'attr' => [
            'class' => 'form-control',
            'id' => $type.'[phone_number]'
        ],
        'required' => TRUE,
    ])

    @include('backend.master.form.fields.address.address', [
        'name' => $type,
        'label' => 'Address',
        'required' => TRUE,
    ])
</div>