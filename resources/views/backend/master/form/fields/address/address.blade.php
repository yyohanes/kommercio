<div class="form-group address-options-group">
    <?php $required = isset($required)?$required:false; ?>
    <label class="control-label col-md-3">{!! $label.($required?' <span class="required">*</span>':'') !!}</label>

    <?php
    $parent = isset($parent)?$parent:null;
    $label = false;
    ?>

    <div class="col-md-9">
        <div class="row">
            <div class="col-md-12">
                @include('backend.master.form.fields.text', [
                    'name' => $name.'[address_1]',
                    'key' => $name.'.address_1',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => $name.'[address_1]',
                        'placeholder' => 'Address 1'.($required?' (required)':'')
                    ]
                ])

                @include('backend.master.form.fields.text', [
                    'name' => $name.'[address_2]',
                    'key' => $name.'address_2',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => $name.'[address_2]',
                        'placeholder' => 'Address 2'
                    ]
                ])
            </div>

            <div class="col-md-6">
                @include('backend.master.form.fields.address.country', [
                     'name' => $name.'[country_id]',
                     'label' => 'Country',
                     'key' => $name.'.country_id',
                     'attr' => [
                         'class' => 'form-control select2',
                         'id' => $name.'[country_id]'
                     ],
                     'label' => FALSE
                 ])
            </div>
            <div class="col-md-6">
                @include('backend.master.form.fields.address.state', [
                    'name' => $name.'[state_id]',
                    'label' => 'State',
                    'key' => $name.'.state_id',
                    'attr' => [
                        'class' => 'form-control select2',
                        'id' => $name.'[state_id]'
                    ],
                    'parent' => old($name.'.country_id', $parent?$parent->country_id:null),
                    'label' => FALSE
                ])
            </div>

            <div class="col-md-6">
                @include('backend.master.form.fields.address.city', [
                    'name' => $name.'[city_id]',
                    'label' => 'City',
                    'key' => $name.'.city_id',
                    'attr' => [
                        'class' => 'form-control select2',
                        'id' => $name.'[city_id]'
                    ],
                    'parent' => old($name.'.state_id', $parent?$parent->state_id:null),
                    'label' => FALSE
                ])
            </div>

            <div class="col-md-6">
                @include('backend.master.form.fields.text', [
                    'name' => $name.'[custom_city]',
                    'label' => 'City',
                    'key' => $name.'.custom_city',
                    'attr' => [
                        'class' => 'form-control custom-city-text',
                        'id' => $name.'[custom_city]',
                        'placeholder' => 'City'
                    ],
                    'label' => FALSE
                ])
            </div>

            <div class="col-md-6">
                @include('backend.master.form.fields.address.district', [
                    'name' => $name.'[district_id]',
                    'label' => 'District',
                    'key' => $name.'.district_id',
                    'attr' => [
                        'class' => 'form-control select2',
                        'id' => $name.'[district_id]'
                    ],
                    'parent' => old($name.'.city_id', $parent?$parent->city_id:null),
                    'label' => FALSE
                ])
            </div>

            <div class="col-md-6">
                @include('backend.master.form.fields.address.area', [
                    'name' => $name.'[area_id]',
                    'label' => 'Area',
                    'key' => $name.'.area_id',
                    'attr' => [
                        'class' => 'form-control select2',
                        'id' => $name.'[area_id]'
                    ],
                    'parent' => old($name.'.district_id', $parent?$parent->district_id:null),
                    'label' => FALSE
                ])
            </div>
            <div class="col-md-6">
                @include('backend.master.form.fields.text', [
                    'name' => $name.'[postal_code]',
                    'label' => 'Postal Code',
                    'key' => $name.'.postal_code',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => $name.'[postal_code]',
                        'placeholder' => 'Postal Code'
                    ],
                    'label' => FALSE
                ])
            </div>
        </div>
    </div>
</div>
