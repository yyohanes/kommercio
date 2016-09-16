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
    'name' => 'class',
    'label' => 'Class',
    'key' => 'class',
    'attr' => [
        'class' => 'form-control',
        'id' => 'class',
    ],
    'help_text' => 'Advanced: Do not touch this part if you don\'t know what you are doing.',
    'required' => TRUE
])

@include('backend.master.form.fields.textarea', [
    'name' => 'message',
    'label' => 'Display Message',
    'key' => 'message',
    'attr' => [
        'class' => 'form-control wysiwyg-editor',
        'id' => 'message',
        'data-height' => 100
    ],
])

@if($additionalFieldsForm)
    @include($additionalFieldsForm)
@endif