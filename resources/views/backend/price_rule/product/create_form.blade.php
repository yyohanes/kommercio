@section('bottom_page_scripts')
    @parent

    <script>
        var $priceRuleOptionGroupMockup = '{!! json_encode(view('backend.price_rule.product.price_rule_option_group')->render()) !!}';
    </script>
    <script src="{{ asset('backend/assets/scripts/pages/price_rule_form.js') }}" type="text/javascript"></script>
@stop

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
    'name' => 'name',
    'label' => 'Name',
    'key' => 'name',
    'attr' => [
        'class' => 'form-control',
        'id' => 'name'
    ],
    'required' => TRUE
])

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
])

@include('backend.master.form.fields.checkbox', [
    'name' => 'is_discount',
    'label' => 'Is Discount',
    'key' => 'is_discount',
    'value' => 1,
    'attr' => [
        'class' => 'make-switch',
        'id' => 'is_discount',
        'data-on-color' => 'warning'
    ],
    'checked' => $priceRule->is_discount,
])

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

<div class="portlet margin-top-30">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject sbold uppercase"> Rules </span>
        </div>
        <div class="actions">
            <a id="price-rule-options-add" href="#" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Add </a>
        </div>
    </div>

    <div class="portlet-body" id="price-rule-option-groups-wrapper">
        <?php
            $priceRuleOptionGroups = old('price_rule_option_groups', []);
        ?>
        @foreach($priceRuleOptionGroups as $idx=>$priceRuleOptionGroup)
            @include('backend.price_rule.product.price_rule_option_group', ['index' => $idx])
        @endforeach
    </div>
</div>