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

@include('backend.master.form.fields.checkbox', [
    'name' => 'taxable',
    'label' => 'Taxable',
    'key' => 'taxable',
    'value' => 1,
    'checked' => $shippingMethod->taxable,
    'attr' => [
        'class' => 'make-switch',
        'id' => 'taxable',
        'data-on-color' => 'warning'
    ],
])

<div class="row form-group">
    <label class="control-label col-md-3">
        Stores
    </label>
    <div class="col-md-5">
        @include('backend.master.form.fields.select', [
            'name' => 'store_scope',
            'label' => null,
            'key' => 'store_scope',
            'attr' => [
                'class' => 'form-control',
                'id' => 'store-scope-select'
            ],
            'options' => ['all' => 'All Stores', 'selected' => 'Selected Stores'],
            'defaultOptions' => $shippingMethod->stores->count() > 0?'selected':'all'
        ])

        <div data-select_dependent="#store-scope-select" data-select_dependent_value="selected">
            @include('backend.master.form.fields.select', [
                'name' => 'stores[]',
                'label' => null,
                'key' => 'stores',
                'attr' => [
                    'class' => 'form-control select2',
                    'id' => 'stores-select',
                    'multiple' => true,
                ],
                'options' => $storeOptions,
                'defaultOptions' => $shippingMethod->stores->pluck('id')->all()
            ])
        </div>
    </div>
</div>

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