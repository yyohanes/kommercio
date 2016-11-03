<div class="portlet light bordered">
    <div class="portlet-title">
        <div class="caption">
            <span class="caption-subject">Bulk Variation Form</span>
        </div>
    </div>

    <div class="portlet-body">
        <div class="panel-group accordion" id="product-variation-form-accordion">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h5 class="panel-title">
                        <a class="accordion-toggle accordion-toggle-styled" data-toggle="collapse" data-parent="#product-variation-form-accordion" href="#variation-basic"> Basic Information </a>
                    </h5>
                </div>
                <div id="variation-basic" class="panel-collapse in">
                    <div class="panel-body">
                        @foreach($attributes as $attribute)
                            <?php
                            $options = [];
                            foreach($attribute->values as $value){
                                $options[$value->id] = $value->name;
                            }
                            ?>
                            <div class="col-md-6">
                                @include('backend.master.form.fields.select', [
                                    'name' => '_bulkAttribute['.$attribute->id.'][]',
                                    'label' => $attribute->name,
                                    'key' => '_bulkAttribute.'.$attribute->id,
                                    'attr' => [
                                        'class' => 'form-control multiselect',
                                        'multiple' => TRUE,
                                        'id' => '_bulkAttribute['.$attribute->id.'][]',
                                    ],
                                    'two_lines' => TRUE,
                                    'options' => $options,
                                ])
                            </div>
                        @endforeach

                        @include('backend.master.form.fields.number', [
                            'name' => 'variation[productDetail][retail_price]',
                            'label' => 'Retail Price',
                            'key' => 'variation.productDetail.retail_price',
                            'attr' => [
                                'class' => 'form-control',
                                'id' => 'variation[productDetail][retail_price]',
                            ],
                            'unit' => CurrencyHelper::getCurrentCurrency()['symbol'],
                            'unitPosition' => 'front',
                            'help_text' => 'If price is left empty, it will inherit from its parent price.',
                        ])

                        @include('backend.master.form.fields.checkbox', [
                            'name' => 'variation[productDetail][available]',
                            'label' => 'Available for Order',
                            'key' => 'variation.productDetail.available',
                            'value' => 1,
                            'checked' => $product->productDetail?$product->productDetail->available:true,
                            'attr' => [
                                'class' => 'make-switch',
                                'id' => 'variation[productDetail][available]',
                                'data-on-color' => 'warning'
                            ],
                            'appends' => '<a class="btn btn-default" href="#variation-availability-schedule-modal" data-toggle="modal"><i class="fa fa-calendar"></i></a>'
                        ])

                        <div id="variation-availability-schedule-modal" class="modal fade" role="dialog" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                        <h4 class="modal-title">Availability Schedule</h4>
                                    </div>
                                    <div class="modal-body">
                                        @include('backend.master.form.fields.datetime', [
                                            'name' => 'variation[productDetail][available_date_from]',
                                            'label' => 'Available From',
                                            'key' => 'variation.productDetail.available_date_from',
                                            'attr' => [
                                                'id' => 'variation[productDetail][available_date_from]'
                                            ],
                                        ])

                                        @include('backend.master.form.fields.datetime', [
                                            'name' => 'variation[productDetail][available_date_to]',
                                            'label' => 'Available Until',
                                            'key' => 'variation.productDetail.available_date_to',
                                            'attr' => [
                                                'id' => 'variation[productDetail][available_date_to]'
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
                            'name' => 'variation[productDetail][active]',
                            'label' => 'Active',
                            'key' => 'variation.productDetail.active',
                            'value' => 1,
                            'checked' => $product->productDetail?$product->productDetail->active:true,
                            'attr' => [
                                'class' => 'make-switch',
                                'id' => 'variation[productDetail][active]',
                                'data-on-color' => 'warning'
                            ],
                            'appends' => '<a class="btn btn-default" href="#variation-active-schedule-modal" data-toggle="modal"><i class="fa fa-calendar"></i></a>'
                        ])

                        <div id="variation-active-schedule-modal" class="modal fade" role="dialog" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                        <h4 class="modal-title">Active Schedule</h4>
                                    </div>
                                    <div class="modal-body">
                                        @include('backend.master.form.fields.datetime', [
                                            'name' => 'variation[productDetail][active_date_from]',
                                            'label' => 'Active From',
                                            'key' => 'variation.productDetail.active_date_from',
                                            'attr' => [
                                                'id' => 'productDetail[active_date_from]'
                                            ],
                                        ])

                                        @include('backend.master.form.fields.datetime', [
                                            'name' => 'variation[productDetail][active_date_to]',
                                            'label' => 'Active Until',
                                            'key' => 'variation.productDetail.active_date_to',
                                            'attr' => [
                                                'id' => 'variation[productDetail][active_date_to]'
                                            ],
                                        ])
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn green" data-dismiss="modal" aria-hidden="true">Done</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h5 class="panel-title">
                        <a class="accordion-toggle accordion-toggle-styled collapsed" data-toggle="collapse" data-parent="#product-variation-form-accordion" href="#variation-images"> Images </a>
                    </h5>
                </div>
                <div id="variation-images" class="panel-collapse collapse">
                    <div class="panel-body">
                        @include('backend.master.form.fields.images_checkbox', [
                            'name' => 'variation[thumbnails]',
                            'label' => 'Thumbnails',
                            'key' => 'variation.thumbnails',
                            'attr' => [
                                'class' => 'form-control',
                                'id' => 'variation[thumbnails]'
                            ],
                            'existing' => $product->thumbnails
                        ])

                        @include('backend.master.form.fields.images_checkbox', [
                            'name' => 'variation[images]',
                            'label' => 'Product Images',
                            'key' => 'variation.images',
                            'attr' => [
                                'class' => 'form-control',
                                'id' => 'variation[images]'
                            ],
                            'existing' => $product->images
                        ])
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h5 class="panel-title">
                        <a class="accordion-toggle accordion-toggle-styled collapsed" data-toggle="collapse" data-parent="#product-variation-form-accordion" href="#variation-shipping"> Shipping Information </a>
                    </h5>
                </div>
                <div id="variation-shipping" class="panel-collapse collapse">
                    <div class="panel-body">
                        <div class="well">If fields below is 0 or empty, it will use inherented value from its parent.</div>

                        @include('backend.master.form.fields.number', [
                            'name' => 'variation[width]',
                            'label' => 'Package Width',
                            'key' => 'variation.width',
                            'attr' => [
                                'class' => 'form-control',
                                'id' => 'variation[width]',
                            ],
                            'unit' => 'cm',
                            'valueColumnClass' => 'col-md-4'
                        ])

                        @include('backend.master.form.fields.number', [
                            'name' => 'variation[length]',
                            'label' => 'Package Length',
                            'key' => 'variation.length',
                            'attr' => [
                                'class' => 'form-control',
                                'id' => 'variation[length]',
                            ],
                            'unit' => 'cm',
                            'valueColumnClass' => 'col-md-4'
                        ])

                        @include('backend.master.form.fields.number', [
                            'name' => 'variation[depth]',
                            'label' => 'Package Depth',
                            'key' => 'variation.depth',
                            'attr' => [
                                'class' => 'form-control',
                                'id' => 'variation[depth]',
                            ],
                            'unit' => 'cm',
                            'valueColumnClass' => 'col-md-4'
                        ])

                        @include('backend.master.form.fields.number', [
                            'name' => 'variation[weight]',
                            'label' => 'Package Weight',
                            'key' => 'variation.weight',
                            'attr' => [
                                'class' => 'form-control',
                                'id' => 'variation[weight]',
                            ],
                            'unit' => 'gr',
                            'valueColumnClass' => 'col-md-4'
                        ])
                    </div>
                </div>
            </div>

            <div class="margin-top-15 text-center">
                {!! Form::hidden('variation[store_id]', ProjectHelper::getActiveStore()->id) !!}
                <button id="variation-save" data-form_token="{{ csrf_token() }}" data-variation_save="{{ route('backend.catalog.product.variation_bulk_save', ['id' => $product->id]) }}" class="btn btn-info"><i class="fa fa-save"></i> Save Variations</button>
                <button id="variation-cancel" class="btn btn-default"><i class="fa fa-remove"></i> Cancel</button>
            </div>
        </div>
    </div>
</div>