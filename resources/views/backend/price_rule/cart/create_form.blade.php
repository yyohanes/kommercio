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

@include('backend.master.form.fields.text', [
    'name' => 'coupon_code',
    'label' => 'Coupon Code',
    'key' => 'coupon_code',
    'attr' => [
        'class' => 'form-control',
        'id' => 'coupon_code'
    ],
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
    'valueColumnClass' => 'col-md-6',
    'options' => $shippingMethodOptions,
    'defaultOptions' => old('shipping', $priceRule->shippingOptionGroup?$priceRule->shippingOptionGroup->shippingMethods->pluck('id')->all():null),
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

<div data-select_dependent="#offer_type" data-select_dependent_value="product_discount">
@include('backend.master.form.fields.select', [
    'name' => 'products[]',
    'label' => 'Specific Products',
    'key' => 'products',
    'attr' => [
        'class' => 'form-control select2-ajax',
        'id' => 'products',
        'multiple' => TRUE,
        'data-remote_source' => route('backend.catalog.product.autocomplete'),
        'data-remote_value_property' => 'sku',
        'data-remote_label_property' => 'name',
    ],
    'required' => FALSE,
    'valueColumnClass' => 'col-md-6',
    'options' => $defaultProducts,
    'defaultOptions' => array_keys($defaultProducts),
    'help_text' => 'You can select more than one Product.'
])
</div>

<div class="portlet margin-top-30" data-select_dependent="#offer_type" data-select_dependent_value="product_discount">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject sbold uppercase"> Specific Options </span>
        </div>
        <div class="actions">
            <a id="price-rule-options-add" href="#" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Add </a>
        </div>
    </div>

    <div class="portlet-body" id="price-rule-option-groups-wrapper">
        <?php
        $priceRuleOptionGroups = old('cart_price_rule_option_groups', []);
        ?>
        @foreach($priceRuleOptionGroups as $idx=>$priceRuleOptionGroup)
            @include('backend.price_rule.cart.price_rule_option_group', ['index' => $idx])
        @endforeach
    </div>
</div>

@section('bottom_page_scripts')
    @parent

    <script>
        var $priceRuleOptionGroupMockup = {!! json_encode(view('backend.price_rule.cart.price_rule_option_group')->render()) !!};
    </script>
    <script src="{{ asset('backend/assets/scripts/pages/cart_price_rule_form.js') }}" type="text/javascript"></script>
@stop