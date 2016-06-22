@include('backend.master.form.fields.email', [
    'name' => 'email',
    'label' => 'Email',
    'key' => 'email',
    'attr' => [
        'class' => 'form-control',
        'id' => 'email'
    ],
    'required' => TRUE
])

@include('backend.master.form.fields.password', [
    'name' => 'password',
    'label' => 'Password',
    'key' => 'password',
    'attr' => [
        'class' => 'form-control',
        'id' => 'password'
    ],
    'required' => !$user->exists,
])

@include('backend.master.form.fields.password', [
    'name' => 'password_confirmation',
    'label' => 'Confirm Password',
    'key' => 'password_confirmation',
    'attr' => [
        'class' => 'form-control',
        'id' => 'password_confirmation'
    ],
    'required' => !$user->exists
])

@include('backend.master.form.fields.select', [
    'name' => 'role',
    'label' => 'Role',
    'key' => 'role',
    'attr' => [
        'class' => 'form-control select2',
        'id' => 'role',
    ],
    'options' => $roleOptions,
    'required' => TRUE,
    'defaultOptions' => old('role', $user->roles->pluck('id')->all())
])

@include('backend.master.form.fields.select', [
    'name' => 'status',
    'label' => 'Status',
    'key' => 'status',
    'attr' => [
        'class' => 'form-control select2',
        'id' => 'status'
    ],
    'options' => \Kommercio\Models\User::getStatusOptions(),
    'required' => TRUE,
])

<hr/>

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

@include('backend.master.form.fields.select', [
    'name' => 'stores[]',
    'label' => 'Stores',
    'key' => 'stores',
    'attr' => [
        'class' => 'form-control select2',
        'id' => 'stores',
        'multiple' => true
    ],
    'options' => $storeOptions,
    'required' => TRUE,
    'defaultOptions' => old('stores', $user->stores->pluck('id')->all())
])