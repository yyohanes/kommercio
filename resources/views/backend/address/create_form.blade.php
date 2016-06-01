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

@if($type != 'country')
@include('backend.master.form.fields.select', [
    'name' => 'parent_id',
    'label' => $address->parentClass,
    'key' => 'parent_id',
    'options' => $parentOptions,
    'attr' => [
        'class' => 'form-control select2',
        'id' => 'parent_id'
    ],
    'defaultOptions' => Request::get('parent_id', null),
    'required' => TRUE
])
@else
    @include('backend.master.form.fields.text', [
        'name' => 'iso_code',
        'label' => 'ISO Code',
        'key' => 'iso_code',
        'attr' => [
            'class' => 'form-control',
            'id' => 'iso_code',
        ],
        'help-block' => '2-characters ISO Code',
        'required' => TRUE
    ])

    @include('backend.master.form.fields.text', [
        'name' => 'country_code',
        'label' => 'Country Code',
        'key' => 'country_code',
        'attr' => [
            'class' => 'form-control',
            'id' => 'country_code',
        ],
        'help-block' => 'Country Code/Phone Prefix',
        'required' => TRUE
    ])
@endif

@if($type != 'area')
    @include('backend.master.form.fields.checkbox', [
        'name' => 'has_descendant',
        'label' => 'Has Descendant?',
        'key' => 'has_descendant',
        'attr' => [
            'class' => 'make-switch',
            'id' => 'has_descendant',
            'data-on-color' => 'warning',
            'data-size' => 'small',
        ],
        'value' => 1,
        'checked' => old('has_descendant', $address->has_descendant)
    ])
@endif

@include('backend.master.form.fields.checkbox', [
    'name' => 'active',
    'label' => 'Active',
    'key' => 'active',
    'attr' => [
        'class' => 'make-switch',
        'id' => 'active',
        'data-on-color' => 'warning',
        'data-size' => 'small',
    ],
    'value' => 1,
    'checked' => old('active', $address->active)
])