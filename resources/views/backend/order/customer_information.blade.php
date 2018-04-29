<div class="customer-information-wrapper">
    <?php
        $emailAttr = [
            'class' => 'form-control',
            'id' => $type.'[email]',
        ];

        if($type == 'profile'){
            $emailAttr += [
                'data-typeahead_remote' => route('backend.customer.autocomplete'),
                'data-typeahead_display' => 'email',
                'data-typeahead_label' => 'name',
                'placeholder' => 'Search Customer',
            ];
        }
    ?>
    @include('backend.master.form.fields.email', [
    'name' => $type.'[email]',
    'label' => 'Email',
    'key' => $type.'.email',
    'attr' => $emailAttr,
    'required' => TRUE,
])

    @include('backend.master.form.fields.text', [
        'name' => $type.'[full_name]',
        'label' => 'Full Name',
        'key' => $type.'.full_name',
        'attr' => [
            'class' => 'form-control',
            'id' => $type.'[full_name]'
        ],
        'required' => TRUE,
    ])

    @include('backend.master.form.fields.tel', [
        'name' => $type.'[phone_number]',
        'label' => 'Phone Number',
        'key' => $type.'.phone_number',
        'attr' => [
            'class' => 'form-control',
            'id' => $type.'[phone_number]'
        ],
        'required' => TRUE,
    ])

    @include('backend.master.form.fields.address.address', [
        'name' => $type,
        'label' => 'Address',
    ])
</div>
