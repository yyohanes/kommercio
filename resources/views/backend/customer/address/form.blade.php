<div class="portlet light bordered">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject">@if($profile->exists) Edit Address @else New Address @endif</span>
        </div>
    </div>

    <div class="portlet-body">
        {!! Form::open(['route' => ['backend.customer.address.save', 'customer_id' => $customer->id, 'id' => $profile->id], 'class' => 'form-horizontal']) !!}
        @include('backend.master.form.fields.select', [
            'name' => 'name',
            'label' => 'Address Name',
            'key' => 'name',
            'attr' => [
                'class' => 'form-control select2',
                'id' => 'name'
            ],
            'options' => ['' => 'Select'] + \Kommercio\Models\Customer::getProfileNameOptions(),
            'required' => TRUE,
        ])

        @include('backend.master.form.fields.select', [
            'name' => 'profile[salute]',
            'label' => 'Salute',
            'key' => 'profile.salute',
            'attr' => [
                'class' => 'form-control select2',
                'id' => 'profile[salute]'
            ],
            'options' => ['' => 'Select'] + \Kommercio\Models\Customer::getSaluteOptions(),
        ])

        @include('backend.master.form.fields.text', [
            'name' => 'profile[full_name]',
            'label' => 'Full Name',
            'key' => 'profile.full_name',
            'attr' => [
                'class' => 'form-control',
                'id' => 'profile[full_name]'
            ],
            'required' => TRUE
        ])

        @include('backend.master.form.fields.tel', [
            'name' => 'profile[phone_number]',
            'label' => 'Phone Number',
            'key' => 'profile.phone_number',
            'attr' => [
                'class' => 'form-control',
                'id' => 'profile[phone_number]'
            ],
            'required' => TRUE
        ])

        @include('backend.master.form.fields.tel', [
            'name' => 'profile[home_phone]',
            'label' => 'Home Phone',
            'key' => 'profile.home_phone',
            'attr' => [
                'class' => 'form-control',
                'id' => 'profile[home_phone]'
            ]
        ])

        @include('backend.master.form.fields.address.address', [
            'name' => 'profile',
            'label' => 'Address',
            'parent' => $profile,
            'required' => TRUE
        ])

        @include('backend.master.form.fields.checkbox', [
            'name' => 'billing',
            'label' => 'Default Billing',
            'key' => 'billing',
            'attr' => [
                'class' => 'make-switch',
                'id' => 'billing',
                'data-on-color' => 'warning',
                'data-size' => 'small',
            ],
            'value' => 1,
            'checked' => old('billing', $billing)
        ])

        @include('backend.master.form.fields.checkbox', [
            'name' => 'shipping',
            'label' => 'Default Shipping',
            'key' => 'shipping',
            'attr' => [
                'class' => 'make-switch',
                'id' => 'shipping',
                'data-on-color' => 'warning',
                'data-size' => 'small',
            ],
            'value' => 1,
            'checked' => old('shipping', $shipping)
        ])

        <div class="margin-top-15 text-center">
            <button id="address-save" data-address_save="{{ route('backend.customer.address.save', ['customer_id' => $customer->id, 'id' => $profile->id]) }}" class="btn btn-info"><i class="fa fa-save"></i> Save Address</button>
            <button id="address-cancel" class="btn btn-default"><i class="fa fa-remove"></i> Cancel</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>