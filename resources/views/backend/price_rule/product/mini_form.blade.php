<div class="portlet light bordered">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject">@if($priceRule->exists) Edit Product Price Rule @else New Price Rule @endif</span>
        </div>
    </div>

    <div class="portlet-body">
        <div class="row">
            <label class="control-label col-md-3">For</label>
            <div class="col-sm-3">
                @include('backend.master.form.fields.select', [
                    'name' => 'price_rule[currency]',
                    'label' => null,
                    'key' => 'price_rule.currency',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => 'price_rule[currency]',
                    ],
                    'options' => $currencyOptions,
                ])
            </div>

            <div class="col-sm-3">
                @include('backend.master.form.fields.select', [
                    'name' => 'price_rule[store_id]',
                    'label' => null,
                    'key' => 'price_rule.store_id',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => 'price_rule[store_id]',
                    ],
                    'options' => $storeOptions,
                ])
            </div>
        </div>

        @if($product->variations->count())
        @include('backend.master.form.fields.select', [
            'name' => 'price_rule[variation_id]',
            'label' => 'Variation',
            'key' => 'price_rule.variation_id',
            'attr' => [
                'class' => 'form-control',
                'id' => 'price_rule[variation_id]',
            ],
            'options' => $variationOptions,
        ])
        @endif

        @include('backend.master.form.fields.number', [
            'name' => 'price_rule[price]',
            'label' => 'Price',
            'key' => 'price_rule.price',
            'attr' => [
                'class' => 'form-control',
                'id' => 'price_rule[price]',
                'data-currency_dependent' => '#price_rule\\[currency\\]',
                'data-number_type' => 'amount',
            ],
            'unit' => CurrencyHelper::getCurrentCurrency()['symbol'],
            'unitPosition' => 'front',
        ])

        @include('backend.master.form.fields.number', [
            'name' => 'price_rule[modification]',
            'label' => 'Modification',
            'key' => 'price_rule.modification',
            'attr' => [
                'class' => 'form-control',
                'id' => 'price_rule[modification]',
                'data-currency_dependent' => '#price_rule\\[currency\\]',
                'data-number_type_dependent' => '#price_rule\\[modification_type\\]',
                'data-number_type' => 'amount',
            ],
            'unit' => CurrencyHelper::getCurrentCurrency()['symbol'],
            'unitPosition' => 'front',
            'help_text' => 'Negative value will reduce price, while positive value will increase price.',
        ])

        @include('backend.master.form.fields.select', [
            'name' => 'price_rule[modification_type]',
            'label' => 'Modification Type',
            'key' => 'price_rule.modification_type',
            'attr' => [
                'class' => 'form-control',
                'id' => 'price_rule[modification_type]',
            ],
            'options' => $reductionTypeOptions,
        ])

        @include('backend.master.form.fields.checkbox', [
            'name' => 'price_rule[active]',
            'label' => 'Active',
            'key' => 'price_rule.active',
            'value' => 1,
            'attr' => [
                'class' => 'make-switch',
                'id' => 'price_rule[active]',
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
                            'name' => 'price_rule[active_date_from]',
                            'label' => 'Active From',
                            'key' => 'price_rule.active_date_from',
                            'attr' => [
                                'id' => 'price_rule[active_date_from]'
                            ],
                        ])

                        @include('backend.master.form.fields.datetime', [
                            'name' => 'price_rule[active_date_to]',
                            'label' => 'Active Until',
                            'key' => 'price_rule.active_date_to',
                            'attr' => [
                                'id' => 'price_rule[active_date_to]'
                            ],
                        ])
                    </div>
                    <div class="modal-footer">
                        <button class="btn green" data-dismiss="modal" aria-hidden="true">Done</button>
                    </div>
                </div>
            </div>
        </div>

        @include('backend.master.form.fields.checkbox', [
            'name' => 'price_rule[is_discount]',
            'label' => 'Is Discount',
            'key' => 'price_rule.is_discount',
            'value' => 1,
            'attr' => [
                'class' => 'make-switch',
                'id' => 'price_rule[is_discount]',
                'data-on-color' => 'warning'
            ],
            'checked' => $priceRule->is_discount
        ])

        <div class="margin-top-15 text-center">
            <button id="price-rule-save" data-form_token="{{ csrf_token() }}" data-price_rule_save="{{ route('backend.price_rule.product.mini_save', ['product_id' => $product->id, 'id' => $priceRule->exists?$priceRule->id:null]) }}" class="btn btn-info"><i class="fa fa-save"></i> Save Price Rule</button>
            <button id="price-rule-cancel" class="btn btn-default"><i class="fa fa-remove"></i> Cancel</button>
        </div>
    </div>
</div>