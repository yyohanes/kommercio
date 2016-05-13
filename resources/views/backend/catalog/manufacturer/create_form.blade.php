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

@include('backend.master.form.fields.images', [
    'name' => 'logo',
    'label' => 'Logo',
    'key' => 'logo',
    'attr' => [
        'class' => 'form-control',
        'id' => 'logo'
    ],
    'multiple' => FALSE,
    'caption' => FALSE,
    'existing' => $manufacturer->logo?[$manufacturer->logo]:null
])