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
    'name' => 'code',
    'label' => 'Code',
    'key' => 'code',
    'attr' => [
        'class' => 'form-control',
        'id' => 'name'
    ],
    'required' => TRUE,
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

<hr/>

@include('backend.master.form.fields.address.address', [
    'name' => 'location',
    'label' => 'Address',
    'parent' => $store,
    'required' => FALSE
])

<hr/>

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

<div class="portlet margin-top-30">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject sbold uppercase"> Contacts </span>
        </div>
    </div>

    <div class="portlet-body">
        <div class="row">
            <div class="col-md-4">
                <p>General</p>
                @include('backend.master.form.fields.text', [
                    'name' => 'contacts[general][name]',
                    'label' => false,
                    'key' => 'contacts.general.name',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => 'contacts[general][name]',
                        'placeholder' => 'Name'
                    ],
                    'defaultValue' => old('contacts.general.name', $store->getData('contacts.general.name'))
                ])
                @include('backend.master.form.fields.text', [
                    'name' => 'contacts[general][email]',
                    'label' => false,
                    'key' => 'contacts.general.email',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => 'contacts[general][email]',
                        'placeholder' => 'Email Address'
                    ],
                    'defaultValue' => old('contacts.general.email', $store->getData('contacts.general.email'))
                ])
            </div>

            <div class="col-md-4">
                <p>Order</p>
                @include('backend.master.form.fields.text', [
                    'name' => 'contacts[order][name]',
                    'label' => false,
                    'key' => 'contacts.order.name',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => 'contacts[order][name]',
                        'placeholder' => 'Name'
                    ],
                    'defaultValue' => old('contacts.order.name', $store->getData('contacts.order.name'))
                ])
                @include('backend.master.form.fields.text', [
                    'name' => 'contacts[order][email]',
                    'label' => false,
                    'key' => 'contacts.order.email',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => 'contacts[order][email]',
                        'placeholder' => 'Email Address'
                    ],
                    'defaultValue' => old('contacts.order.email', $store->getData('contacts.order.email'))
                ])
            </div>
        </div>
    </div>
</div>