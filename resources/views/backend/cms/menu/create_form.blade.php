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

@include('backend.master.form.fields.text', [
    'name' => 'slug',
    'label' => 'Machine Name',
    'key' => 'slug',
    'attr' => [
        'class' => 'form-control',
        'id' => 'slug',
        'data-slug_source' => '#name'
    ],
    'help_text' => 'Advanced: Automatically generated. Only change if you know what you are doing.',
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