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

@include('backend.master.form.fields.select', [
    'name' => 'type',
    'label' => 'Type',
    'key' => 'type',
    'attr' => [
        'class' => 'form-control',
        'id' => 'type',
    ],
    'options' => $typeOptions,
    'required' => TRUE
])

@foreach($typeOptions as $typeOptionId => $typeOption)
    @include('backend.catalog.product_configuration.type.'.$typeOptionId, ['type' => $typeOptionId])
@endforeach

@include('backend.master.form.fields.checkbox', [
    'name' => 'required',
    'label' => 'Required',
    'key' => 'required',
    'value' => 1,
    'checked' => old('required', $required),
    'attr' => [
        'class' => 'make-switch',
        'id' => 'required',
        'data-on-color' => 'warning'
    ]
])