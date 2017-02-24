@include('backend.master.form.fields.text', [
    'name' => 'name',
    'label' => 'Name',
    'key' => 'name',
    'attr' => [
        'class' => 'form-control',
        'id' => 'name'
    ],
    'required' => TRUE,
    'valueColumnClass' => 'col-md-4'
])

@include('backend.master.form.fields.textarea', [
    'name' => 'description',
    'label' => 'Description',
    'key' => 'description',
    'attr' => [
        'class' => 'form-control wysiwyg-editor',
        'id' => 'description'
    ]
])