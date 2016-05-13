@include('backend.master.form.fields.text', [
    'name' => 'name',
    'label' => 'Name',
    'key' => 'name',
    'attr' => [
        'class' => 'form-control',
        'id' => 'name'
    ],
    'required' => TRUE
])

@include('backend.master.form.fields.textarea', [
    'name' => 'address',
    'label' => 'Address',
    'key' => 'address',
    'attr' => [
        'class' => 'form-control',
        'id' => 'address'
    ],
])