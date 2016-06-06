@include('backend.master.form.fields.text', [
    'name' => 'name',
    'label' => 'Name',
    'key' => 'name',
    'attr' => [
        'class' => 'form-control',
        'id' => 'name'
    ],
    'required' => TRUE,
    'valueColumnClass' => 'col-md-6',
])

@include('backend.master.form.fields.select', [
    'name' => 'offer_type',
    'label' => 'Offer Type',
    'key' => 'offer_type',
    'attr' => [
        'class' => 'form-control',
        'id' => 'offer_type',
    ],
    'options' => $offerTypeOptions,
    'valueColumnClass' => 'col-md-6',
])

<div id="prices-detail-wrapper" data-select_dependent="#offer_type" data-select_dependent_not_value="free_shipping">
@include('backend.master.form.fields.number', [
    'name' => 'price',
    'label' => 'Price',
    'key' => 'price',
    'attr' => [
        'class' => 'form-control',
        'id' => 'price',
        'data-currency_dependent' => '#currency',
        'data-number_type' => 'amount',
    ],
    'unit' => CurrencyHelper::getCurrentCurrency()['symbol'],
    'unitPosition' => 'front',
    'valueColumnClass' => 'col-md-6',
])

@include('backend.master.form.fields.number', [
    'name' => 'modification',
    'label' => 'Modification',
    'key' => 'modification',
    'attr' => [
        'class' => 'form-control',
        'id' => 'modification',
        'data-currency_dependent' => '#currency',
        'data-number_type_dependent' => '#modification_type',
        'data-number_type' => 'amount',
    ],
    'unit' => CurrencyHelper::getCurrentCurrency()['symbol'],
    'unitPosition' => 'front',
    'help_text' => 'Negative value will reduce price, while positive value will increase price.',
    'valueColumnClass' => 'col-md-6',
])

@include('backend.master.form.fields.select', [
    'name' => 'modification_type',
    'label' => 'Modification Type',
    'key' => 'modification_type',
    'attr' => [
        'class' => 'form-control',
        'id' => 'modification_type',
    ],
    'options' => $reductionTypeOptions,
    'valueColumnClass' => 'col-md-6',
])
</div>

@include('backend.master.form.fields.checkbox', [
    'name' => 'active',
    'label' => 'Active',
    'key' => 'active',
    'value' => 1,
    'attr' => [
        'class' => 'make-switch',
        'id' => 'active',
        'data-on-color' => 'warning'
    ],
    'checked' => $priceRule->active,
    'appends' => '<a class="btn btn-default" href="#price-rule-active-schedule-modal" data-toggle="modal"><i class="fa fa-calendar"></i></a>'
])

<div id="price-rule-active-schedule-modal" class="modal fade" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title">Active Schedule</h4>
            </div>
            <div class="modal-body">
                @include('backend.master.form.fields.datetime', [
                    'name' => 'active_date_from',
                    'label' => 'Active From',
                    'key' => 'active_date_from',
                    'attr' => [
                        'id' => 'active_date_from'
                    ],
                ])

                @include('backend.master.form.fields.datetime', [
                    'name' => 'active_date_to',
                    'label' => 'Active Until',
                    'key' => 'active_date_to',
                    'attr' => [
                        'id' => 'active_date_to'
                    ],
                ])
            </div>
            <div class="modal-footer">
                <button class="btn green" data-dismiss="modal" aria-hidden="true">Done</button>
            </div>
        </div>
    </div>
</div>

<hr/>

@include('backend.master.form.fields.number', [
    'name' => 'max_usage',
    'label' => 'Max Usage',
    'key' => 'max_usage',
    'attr' => [
        'class' => 'form-control',
        'id' => 'max_usage'
    ],
    'unitPosition' => 'front',
    'valueColumnClass' => 'col-md-6',
    'help_text' => 'If left empty, it is considered Unlimited.'
])

@include('backend.master.form.fields.number', [
    'name' => 'max_usage_per_customer',
    'label' => 'Max Usage per Customer',
    'key' => 'max_usage_per_customer',
    'attr' => [
        'class' => 'form-control',
        'id' => 'max_usage_per_customer'
    ],
    'unitPosition' => 'front',
    'valueColumnClass' => 'col-md-6',
    'help_text' => 'If left empty, it is considered Unlimited.'
])

<hr/>

<div class="row">
    <label class="control-label col-md-3">For</label>
    <div class="col-sm-3">
        @include('backend.master.form.fields.select', [
            'name' => 'currency',
            'label' => null,
            'key' => 'currency',
            'attr' => [
                'class' => 'form-control',
                'id' => 'currency',
            ],
            'options' => $currencyOptions,
        ])
    </div>

    <div class="col-sm-3">
        @include('backend.master.form.fields.select', [
            'name' => 'store_id',
            'label' => null,
            'key' => 'store_id',
            'attr' => [
                'class' => 'form-control',
                'id' => 'store_id',
            ],
            'options' => $storeOptions,
        ])
    </div>
</div>

@include('backend.master.form.fields.text', [
    'name' => 'customer',
    'label' => 'Specific Customer',
    'key' => 'customer',
    'attr' => [
        'class' => 'form-control',
        'id' => 'customer',
        'data-typeahead_remote' => route('backend.customer.autocomplete'),
        'data-typeahead_display' => 'email',
        'data-typeahead_label' => 'name',
        'placeholder' => 'Search Customer'
    ],
    'defaultValue' => old('customer', $priceRule->customer?$priceRule->customer->getProfile()->email:null),
    'valueColumnClass' => 'col-md-6',
])

<div class="portlet margin-top-30">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject sbold uppercase"> Rules </span>
        </div>
    </div>

    <div class="portlet-body" id="price-rule-option-groups-wrapper">
        @include('backend.master.form.fields.number', [
            'name' => 'minimum_subtotal',
            'label' => 'Minimum Subtotal',
            'key' => 'minimum_subtotal',
            'attr' => [
                'class' => 'form-control',
                'id' => 'minimum_subtotal',
                'data-currency_dependent' => '#currency',
                'data-number_type' => 'amount',
            ],
            'unit' => CurrencyHelper::getCurrentCurrency()['symbol'],
            'unitPosition' => 'front',
            'valueColumnClass' => 'col-md-6',
        ])

        @include('backend.master.form.fields.select', [
            'name' => 'shipping[]',
            'label' => 'Shipping',
            'key' => 'shipping',
            'attr' => [
                'class' => 'form-control',
                'id' => 'shipping',
                'multiple' => TRUE
            ],
            'options' => $shippingMethodOptions,
            'defaultOptions' => old('shipping', $priceRule->shippingOptionGroup?$priceRule->shippingOptionGroup->shippingMethods->pluck('id')->all():null),
            'valueColumnClass' => 'col-md-6',
        ])
    </div>
</div>