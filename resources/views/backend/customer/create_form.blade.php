@include('backend.master.form.fields.email', [
    'name' => 'profile[email]',
    'label' => 'Email',
    'key' => 'profile.email',
    'attr' => [
        'class' => 'form-control',
        'id' => 'profile[email]'
    ]
])

@include('backend.master.form.fields.checkbox', [
    'name' => 'user[create_account]',
    'label' => 'Login Account',
    'key' => 'user.create_account',
    'attr' => [
        'class' => 'make-switch',
        'id' => 'user[create_account]',
        'data-on-color' => 'warning',
        'data-size' => 'small',
    ],
    'value' => 1,
    'checked' => old('user.create_account', isset($customer->user))
])

<div data-enabled-dependent="user\[create_account\]">
    @include('backend.master.form.fields.password', [
        'name' => 'user[password]',
        'label' => 'Password',
        'key' => 'user.password',
        'attr' => [
            'class' => 'form-control',
            'id' => 'user[password]'
        ],
        'required' => TRUE,
    ])

    @include('backend.master.form.fields.password', [
        'name' => 'user[password_confirmation]',
        'label' => 'Confirm Password',
        'key' => 'user.password_confirmation',
        'attr' => [
            'class' => 'form-control',
            'id' => 'user[password_confirmation]'
        ],
    ])

    @include('backend.master.form.fields.select', [
        'name' => 'user[status]',
        'label' => 'Status',
        'key' => 'user.status',
        'attr' => [
            'class' => 'form-control',
            'id' => 'user[status]'
        ],
        'options' => \Kommercio\Models\User::getStatusOptions(),
        'required' => TRUE,
    ])
</div>

<hr/>

@include('backend.master.form.fields.select', [
    'name' => 'profile[salute]',
    'label' => 'Salute',
    'key' => 'profile.salute',
    'attr' => [
        'class' => 'form-control select2',
        'id' => 'profile[salute]'
    ],
    'options' => ['' => 'Select'] + \Kommercio\Models\Customer::getSaluteOptions(),
])

@include('backend.master.form.fields.text', [
    'name' => 'profile[full_name]',
    'label' => 'Full Name',
    'key' => 'profile.full_name',
    'attr' => [
        'class' => 'form-control',
        'id' => 'profile[full_name]'
    ],
    'required' => TRUE
])

@include('backend.master.form.fields.tel', [
    'name' => 'profile[phone_number]',
    'label' => 'Phone Number',
    'key' => 'profile.phone_number',
    'attr' => [
        'class' => 'form-control',
        'id' => 'profile[phone_number]'
    ]
])

@include('backend.master.form.fields.tel', [
    'name' => 'profile[home_phone]',
    'label' => 'Home Phone',
    'key' => 'profile.home_phone',
    'attr' => [
        'class' => 'form-control',
        'id' => 'profile[home_phone]'
    ]
])

@include('backend.master.form.fields.address.address', [
    'name' => 'profile',
    'label' => 'Address',
    'parent' => $customer->getProfile(),
    'required' => FALSE
])

@include('backend.master.form.fields.text', [
    'name' => 'profile[birthday]',
    'label' => 'Birthday',
    'key' => 'profile.birthday',
    'attr' => [
        'class' => 'form-control date-picker',
        'data-date-format' => 'yyyy-mm-dd',
        'id' => 'profile[birthday]',
        'placeholder' => 'YYYY-MM-DD'
    ]
])

{!! Form::hidden('store_id', ProjectHelper::getActiveStore()->id) !!}