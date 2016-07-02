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
    'name' => 'description',
    'label' => 'Description',
    'key' => 'description',
    'attr' => [
        'class' => 'form-control',
        'id' => 'description'
    ]
])