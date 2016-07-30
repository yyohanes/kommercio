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

@include('backend.master.form.fields.address.address', [
    'name' => 'location',
    'label' => 'Address',
    'parent' => $warehouse,
    'required' => TRUE
])