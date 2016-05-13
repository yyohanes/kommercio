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
        'id' => 'type'
    ],
    'options' => \Kommercio\Models\Store::getTypeOptions(),
    'required' => TRUE,
])

@include('backend.master.form.fields.select', [
    'name' => 'warehouses[]',
    'label' => 'Warehouse',
    'key' => 'warehouses',
    'attr' => [
        'class' => 'form-control',
        'id' => 'warehouses[]',
        'multiple' => TRUE
    ],
    'defaultOptions' => old('warehouses', $store->warehouses->pluck('id')->all()),
    'options' => \Kommercio\Models\Warehouse::getWarehouseOptions(),
    'required' => TRUE,
])