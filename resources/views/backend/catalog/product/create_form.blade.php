@section('bottom_page_scripts')
    @parent

    <script>
        global_vars.get_related_product = '{{ route('backend.catalog.product.get_related') }}';
    </script>

    <script src="{{ asset('backend/assets/scripts/pages/product_form.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/scripts/pages/product_form_price.js') }}" type="text/javascript"></script>
    <script src="{{ asset('backend/assets/scripts/pages/product_form_features.js') }}" type="text/javascript"></script>
@stop

<?php
    $store = ProjectHelper::getActiveStore();
    $warehouse = $store->getDefaultWarehouse();
?>

<div class="tabbable-bordered">
    <ul class="nav nav-tabs" role="tablist">
        <li class="active" role="presentation">
            <a href="#tab_general" data-toggle="tab"> General </a>
        </li>
        <li>
            <a href="#tab_price" data-toggle="tab"> Price </a>
        </li>
        <li data-tab_context="variations" role="presentation">
            <a href="#tab_variations" data-toggle="tab"> Variations </a>
        </li>
        <li role="presentation">
            <a href="#tab_category" data-toggle="tab"> Category </a>
        </li>
        <li role="presentation">
            <a href="#tab_images" data-toggle="tab"> Images </a>
        </li>
        <li role="presentation">
            <a href="#tab_features" data-toggle="tab"> Features </a>
        </li>
        <li role="presentation">
            <a href="#tab_inventory" data-toggle="tab"> Inventory </a>
        </li>
        <li role="presentation">
            <a href="#tab_shipping" data-toggle="tab"> Shipping </a>
        </li>
        <li role="presentation">
            <a href="#tab_meta" data-toggle="tab"> Meta </a>
        </li>
        <li role="presentation">
            <a href="#tab_related" data-toggle="tab"> Related </a>
        </li>
        <li role="presentation">
            <a href="#tab_misc" data-toggle="tab"> Misc </a>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="tab_general">
            <div class="form-body">
                <?php
                    $combinationSelectOptions = [
                        'name' => 'combination_type',
                        'label' => 'Type',
                        'key' => 'combination_type',
                        'attr' => [
                                'class' => 'form-control',
                                'id' => 'combination_type',
                        ],
                        'options' => $product->getCombinationTypeOptions(),
                        'required' => TRUE,
                        'valueColumnClass' => 'col-md-4'
                    ];

                    if($product->id){
                        $combinationSelectOptions['attr']['disabled'] = TRUE;
                    }
                ?>
                @include('backend.master.form.fields.select', $combinationSelectOptions)

                <hr/>

                @include('backend.master.form.fields.text', [
                    'name' => 'name',
                    'label' => 'Name',
                    'key' => 'name',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => 'name',
                    ],
                    'required' => TRUE
                ])

                @include('backend.master.form.fields.text', [
                    'name' => 'sku',
                    'label' => 'SKU',
                    'key' => 'sku',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => 'sku',
                    ],
                    'required' => TRUE
                ])

                @include('backend.master.form.fields.textarea', [
                    'name' => 'description_short',
                    'label' => 'Short Description',
                    'key' => 'description_short',
                    'attr' => [
                        'class' => 'form-control wysiwyg-editor',
                        'id' => 'description_short',
                        'data-height' => 100
                    ],
                ])

                @include('backend.master.form.fields.textarea', [
                    'name' => 'description',
                    'label' => 'Description',
                    'key' => 'description',
                    'attr' => [
                        'class' => 'form-control wysiwyg-editor',
                        'id' => 'description',
                    ],
                ])

                @include('backend.master.form.fields.select', [
                    'name' => 'manufacturer_id',
                    'label' => 'Manufacturer',
                    'key' => 'manufacturer_id',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => 'manufacturer_id',
                    ],
                    'options' => ['' => 'Select Manufacturer'] + \Kommercio\Models\Manufacturer::getOptions(),
                    'valueColumnClass' => 'col-md-4'
                ])

                @include('backend.master.form.fields.checkbox', [
                    'name' => 'productDetail[taxable]',
                    'label' => 'Taxable',
                    'key' => 'productDetail.taxable',
                    'value' => 1,
                    'checked' => $product->productDetail?$product->productDetail->taxable:true,
                    'attr' => [
                        'class' => 'make-switch',
                        'id' => 'productDetail[taxable]',
                        'data-on-color' => 'warning'
                    ],
                ])

                <hr/>

                @include('backend.master.form.fields.select', [
                    'name' => 'productDetail[visibility]',
                    'label' => 'Visibility',
                    'key' => 'productDetail.visibility',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => 'productDetail[visibility]',
                    ],
                    'options' => \Kommercio\Models\ProductDetail::getVisibilityOptions(),
                    'required' => TRUE,
                    'valueColumnClass' => 'col-md-4'
                ])

                @include('backend.master.form.fields.checkbox', [
                    'name' => 'productDetail[new]',
                    'label' => 'New',
                    'key' => 'productDetail.new',
                    'value' => 1,
                    'checked' => $product->productDetail?$product->productDetail->new:false,
                    'attr' => [
                        'class' => 'make-switch',
                        'id' => 'productDetail[new]',
                        'data-on-color' => 'warning'
                    ],
                    'appends' => '<a class="btn btn-default" href="#new-schedule-modal" data-toggle="modal"><i class="fa fa-calendar"></i></a>'
                ])

                <div id="new-schedule-modal" class="modal fade" role="dialog" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                <h4 class="modal-title">New Period</h4>
                            </div>
                            <div class="modal-body">
                                @include('backend.master.form.fields.datetime', [
                                    'name' => 'productDetail[new_date_from]',
                                    'label' => 'New From',
                                    'key' => 'productDetail.new_date_from',
                                    'attr' => [
                                        'id' => 'productDetail[new_date_from]'
                                    ],
                                ])

                                @include('backend.master.form.fields.datetime', [
                                    'name' => 'productDetail[new_date_to]',
                                    'label' => 'New Until',
                                    'key' => 'productDetail.new_date_to',
                                    'attr' => [
                                        'id' => 'productDetail[new_date_to]'
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
                    'name' => 'productDetail[available]',
                    'label' => 'Available for Order',
                    'key' => 'productDetail.available',
                    'value' => 1,
                    'checked' => $product->productDetail?$product->productDetail->available:true,
                    'attr' => [
                        'class' => 'make-switch',
                        'id' => 'productDetail[available]',
                        'data-on-color' => 'warning'
                    ],
                    'appends' => '<a class="btn btn-default" href="#availability-schedule-modal" data-toggle="modal"><i class="fa fa-calendar"></i></a>'
                ])

                <div id="availability-schedule-modal" class="modal fade" role="dialog" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                <h4 class="modal-title">Availability Schedule</h4>
                            </div>
                            <div class="modal-body">
                                @include('backend.master.form.fields.datetime', [
                                    'name' => 'productDetail[available_date_from]',
                                    'label' => 'Available From',
                                    'key' => 'productDetail.available_date_from',
                                    'attr' => [
                                        'id' => 'productDetail[available_date_from]'
                                    ],
                                ])

                                @include('backend.master.form.fields.datetime', [
                                    'name' => 'productDetail[available_date_to]',
                                    'label' => 'Available Until',
                                    'key' => 'productDetail.available_date_to',
                                    'attr' => [
                                        'id' => 'productDetail[available_date_to]'
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
                    'name' => 'productDetail[active]',
                    'label' => 'Active',
                    'key' => 'productDetail.active',
                    'value' => 1,
                    'checked' => $product->productDetail?$product->productDetail->active:true,
                    'attr' => [
                        'class' => 'make-switch',
                        'id' => 'productDetail[active]',
                        'data-on-color' => 'warning'
                    ],
                    'appends' => '<a class="btn btn-default" href="#active-schedule-modal" data-toggle="modal"><i class="fa fa-calendar"></i></a>'
                ])

                @include('backend.master.form.fields.number', [
                    'name' => 'productDetail[sort_order]',
                    'label' => 'Sort Order',
                    'key' => 'productDetail.sort_order',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => 'productDetail[sort_order]',
                    ],
                    'valueColumnClass' => 'col-md-2',
                    'unitPosition' => 'front',
                ])

                <div id="active-schedule-modal" class="modal fade" role="dialog" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                                <h4 class="modal-title">Active Schedule</h4>
                            </div>
                            <div class="modal-body">
                                @include('backend.master.form.fields.datetime', [
                                    'name' => 'productDetail[active_date_from]',
                                    'label' => 'Active From',
                                    'key' => 'productDetail.active_date_from',
                                    'attr' => [
                                        'id' => 'productDetail[active_date_from]'
                                    ],
                                ])

                                @include('backend.master.form.fields.datetime', [
                                    'name' => 'productDetail[active_date_to]',
                                    'label' => 'Active Until',
                                    'key' => 'productDetail.active_date_to',
                                    'attr' => [
                                        'id' => 'productDetail[active_date_to]'
                                    ],
                                ])
                            </div>
                            <div class="modal-footer">
                                <button class="btn green" data-dismiss="modal" aria-hidden="true">Done</button>
                            </div>
                        </div>
                    </div>
                </div>

                {!! Form::hidden('store_id', $store->id) !!}
            </div>
        </div>

        <div class="tab-pane" role="tabpanel" id="tab_price">
            <div class="form-body">
                @include('backend.master.form.fields.number', [
                    'name' => 'productDetail[retail_price]',
                    'label' => 'Retail Price',
                    'key' => 'productDetail.retail_price',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => 'productDetail[retail_price]',
                        'data-currency_dependent' => '#productDetail\\[currency\\]',
                        'data-number_type' => 'amount',
                    ],
                    'unit' => CurrencyHelper::getCurrentCurrency()['symbol'],
                    'valueColumnClass' => 'col-md-4',
                    'unitPosition' => 'front',
                    'required' => TRUE
                ])

                @include('backend.master.form.fields.select', [
                    'name' => 'productDetail[currency]',
                    'label' => 'Currency',
                    'key' => 'productDetail.currency',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => 'productDetail[currency]',
                    ],
                    'options' => $currencyOptions,
                    'valueColumnClass' => 'col-md-4',
                    'defaultOptions' => old('productDetail.currency', $product->productDetail?$product->productDetail->currency:CurrencyHelper::getCurrentCurrency()['iso'])
                ])

                <div class="margin-top-30 portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject"> Price Rules </span>
                        </div>

                        @if($product->exists)
                        <div class="actions">
                            <a id="price-rule-add-btn" href="#" class="btn grey-salt btn-sm">
                                <i class="fa fa-plus"></i> Add Price Rule </a>
                        </div>
                        @endif
                    </div>

                    <div class="portlet-body">
                        @if(!$product->exists)
                            You need to save this product first to create price rules.
                        @else
                            <div id="price-rule-form-wrapper"
                                 data-price_rule_form="{{ route('backend.price_rule.product.mini_form', ['product_id' => $product->id]) }}"
                                 data-price_rule_index="{{ route('backend.price_rule.product.mini_index', ['product_id' => $product->id]) }}"
                                 data-form_token="{{ csrf_token() }}">
                            </div>

                            <div id="price-rules-wrapper">
                            <?php $priceRules = $product->priceRules; ?>
                            @include('backend.price_rule.product.mini_index')
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane" role="tabpanel" id="tab_variations">
            <div class="form-body">
                @if(!$product->exists)
                    You need to save this product first to create variations.
                @else
                    <div class="margin-bottom-10">
                        <a class="btn btn-default" id="product-variation-add-btn" href="#">
                            <i class="icon-plus"></i> Add Variation
                        </a>
                    </div>

                    <div id="product-variation-form-wrapper"
                         data-variation_form="{{ route('backend.catalog.product.variation_form', ['id' => $product->id]) }}"
                         data-variation_index="{{ route('backend.catalog.product.variation_index', ['id' => $product->id]) }}"
                         data-form_token="{{ csrf_token() }}"></div>

                    <div class="table-scrollable">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th> SKU </th>
                                <th> Price </th>
                                <th> Net Price </th>
                                <th> Attributes </th>
                                <th> Actions </th>
                            </tr>
                            </thead>
                            <tbody id="product-variations-wrapper">
                            <?php $variations = $product->variations; ?>
                            @include('backend.catalog.product.product_variation_index')
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="tab-pane" role="tabpanel" id="tab_category">
            <div class="form-body">
                @include('backend.master.form.fields.product_categories_checkbox_tree', [
                    'name' => 'categories[]',
                    'label' => 'Associated Categories',
                    'key' => 'categories',
                    'attr' => [
                        'class' => 'form-control height-auto',
                        'id' => 'categories-checkbox'
                    ],
                    'existing' => $product->categories->pluck('id')->all()
                ])

                @include('backend.master.form.fields.select', [
                    'name' => 'default_category',
                    'label' => 'Default Category',
                    'key' => 'default_category',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => 'default_category',
                        'data-default' => old('default_category', ($product->defaultCategory?$product->defaultCategory->id:0))
                    ],
                    'options' => $product->categories->pluck('name', 'id')->all(),
                    'valueColumnClass' => 'col-md-4'
                ])
            </div>
        </div>

        <div class="tab-pane" role="tabpanel" id="tab_images">
            <div class="form-body">
                @include('backend.master.form.fields.images', [
                    'name' => 'thumbnails',
                    'label' => 'Thumbnails',
                    'key' => 'thumbnails',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => 'thumbnails'
                    ],
                    'multiple' => TRUE,
                    'existing' => $product->thumbnails
                ])

                @include('backend.master.form.fields.images', [
                    'name' => 'images',
                    'label' => 'Product Images',
                    'key' => 'images',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => 'images'
                    ],
                    'multiple' => TRUE,
                    'existing' => $product->images
                ])
            </div>
        </div>

        <div class="tab-pane" role="tabpanel" id="tab_features">
            <div class="form-body">
                @if(!$product->exists)
                    You need to save this product first to add features.
                @else
                    <div id="product-features-form-wrapper"
                         data-feature_index="{{ route('backend.catalog.product.feature_index', ['id' => $product->id]) }}"
                         data-form_token="{{ csrf_token() }}"></div>

                    <div id="product-features-wrapper">
                        @include('backend.catalog.product.product_feature_index')
                    </div>
                @endif
            </div>
        </div>

        <div class="tab-pane" role="tabpanel" id="tab_shipping">
            <div class="form-body">
                @include('backend.master.form.fields.number', [
                    'name' => 'width',
                    'label' => 'Package Width',
                    'key' => 'width',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => 'width',
                    ],
                    'unit' => 'cm',
                    'valueColumnClass' => 'col-md-4'
                ])

                @include('backend.master.form.fields.number', [
                    'name' => 'length',
                    'label' => 'Package Length',
                    'key' => 'length',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => 'length',
                    ],
                    'unit' => 'cm',
                    'valueColumnClass' => 'col-md-4'
                ])

                @include('backend.master.form.fields.number', [
                    'name' => 'depth',
                    'label' => 'Package Depth',
                    'key' => 'depth',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => 'depth',
                    ],
                    'unit' => 'cm',
                    'valueColumnClass' => 'col-md-4'
                ])

                @include('backend.master.form.fields.number', [
                    'name' => 'weight',
                    'label' => 'Package Weight',
                    'key' => 'weight',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => 'weight',
                    ],
                    'unit' => 'gr',
                    'valueColumnClass' => 'col-md-4'
                ])
            </div>
        </div>

        <div class="tab-pane" role="tabpanel" id="tab_inventory">
            <div class="form-body">
                @if(!$product->exists)
                    You need to save this product first to manage inventory.
                @else
                    @if($product->variations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-advance">
                                <thead>
                                <tr>
                                    <th style="width: 40%;">Variation</th>
                                    <th>Manage Stock</th>
                                    <th>Stock</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($product->variations as $variation)
                                    <tr>
                                        <td>{{ $variation->name }}</td>
                                        <td>
                                            @include('backend.master.form.fields.checkbox', [
                                                'name' => 'variation['.$variation->id.'][productDetail][manage_stock]',
                                                'key' => 'variation.'.$variation->id.'.productDetail.manage_stock',
                                                'value' => 1,
                                                'checked' => old('variation.'.$variation->id.'.productDetail.manage_stock', $variation->productDetail->manage_stock),
                                                'attr' => [
                                                    'class' => 'make-switch',
                                                    'id' => 'variation['.$variation->id.'][productDetail][manage_stock]',
                                                    'data-on-color' => 'warning',
                                                    'data-size' => 'small',
                                                ],
                                            ])
                                        </td>
                                        <td>
                                            @include('backend.master.form.fields.number', [
                                                'name' => 'variation['.$variation->id.'][stock]',
                                                'key' => 'variation.'.$variation->id.'.stock',
                                                'attr' => [
                                                    'class' => 'form-control',
                                                    'id' => 'variation['.$variation->id.'][stock]',
                                                    'data-enabled-dependent' => 'variation\\['.$variation->id.'\\]\\[productDetail\\]\\[manage_stock\\]',
                                                    'data-enabled-dependent-effect' => 'disabled',
                                                ],
                                                'unit' => 'pcs',
                                                'valueColumnClass' => 'col-md-8',
                                                'defaultValue' => old('variation.'.$variation->id.'.stock', $variation->getStock($warehouse->id))
                                            ])
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        @include('backend.master.form.fields.checkbox', [
                            'name' => 'productDetail[manage_stock]',
                            'label' => 'Manage Stock',
                            'key' => 'productDetail.manage_stock',
                            'value' => 1,
                            'checked' => old('productDetail.manage_stock', $product->productDetail?$product->productDetail->manage_stock:false),
                            'attr' => [
                                'class' => 'make-switch',
                                'id' => 'productDetail[manage_stock]',
                                'data-on-color' => 'warning',
                                'data-size' => 'small',
                            ],
                        ])

                        @include('backend.master.form.fields.number', [
                            'name' => 'stock',
                            'label' => 'Stock',
                            'key' => 'stock',
                            'attr' => [
                                'class' => 'form-control',
                                'id' => 'stock',
                                'data-enabled-dependent' => 'productDetail\\[manage_stock\\]',
                                'data-enabled-dependent-effect' => 'disabled',
                            ],
                            'unit' => 'pcs',
                            'valueColumnClass' => 'col-md-4',
                            'defaultValue' => old('stock', $product->getStock($warehouse->id))
                        ])
                    @endif
                @endif

                {!! Form::hidden('warehouse_id', $warehouse->id) !!}
            </div>
        </div>

        <div class="tab-pane" role="tabpanel" id="tab_meta">
            <div class="form-body">
                @include('backend.master.form.fields.text', [
                    'name' => 'slug',
                    'label' => 'Friendly URL',
                    'key' => 'slug',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => 'slug',
                        'data-slug_source' => '#name'
                    ],
                    'required' => TRUE
                ])

                @include('backend.master.form.fields.text', [
                    'name' => 'meta_title',
                    'label' => 'Meta Title',
                    'key' => 'meta_title',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => 'meta_title'
                    ]
                ])

                @include('backend.master.form.fields.textarea', [
                    'name' => 'meta_description',
                    'label' => 'Meta Description',
                    'key' => 'meta_description',
                    'attr' => [
                        'class' => 'form-control',
                        'id' => 'meta_description'
                    ]
                ])
            </div>
        </div>

        <div class="tab-pane" role="tabpanel" id="tab_related">
            <div class="form-body">
                <div class="form-group">
                    <label for="thumbnails" class="control-label col-md-3">Cross-sell Products</label>
                    <div class="col-md-9">
                        @include('backend.master.form.fields.text', [
                            'name' => 'find_cross_sell_product',
                            'label' => false,
                            'key' => 'find_cross_sell_product',
                            'attr' => [
                                'class' => 'form-control product-relation-finder',
                                'id' => 'find_cross_sell_product',
                                'placeholder' => 'Find product name...',
                                'data-product_relation_type' => 'cross_sell',
                                'data-typeahead_remote' => route('backend.catalog.product.autocomplete'),
                                'data-typeahead_display' => 'sku',
                                'data-typeahead_label' => 'name',
                                'data-typeahead_additional_query' => '&entity_only=1&exclude='.$product->id
                            ],
                            'valueColumnClass' => 'col-md-6'
                        ])

                        <div id="cross_sell-products">
                            @foreach(old('cross_sell_product', $product->crossSellTo->pluck('id')) as $crossSell)
                                <?php $crossSellObj = \Kommercio\Models\Product::findOrFail($crossSell); ?>
                                @include('backend.catalog.product.product_relation_result', ['product' => $crossSellObj, 'relation' => 'cross_sell'])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane" role="tabpanel" id="tab_misc">
            <div class="form-body">
                @include('backend.master.form.fields.checkbox', [
                    'name' => 'productDetail[sticky_line_item]',
                    'label' => 'Sticky Line Item',
                    'key' => 'productDetail.sticky_line_item',
                    'value' => 1,
                    'checked' => $product->productDetail?$product->productDetail->sticky_line_item:false,
                    'attr' => [
                        'class' => 'make-switch',
                        'id' => 'productDetail[sticky_line_item]',
                        'data-on-color' => 'warning'
                    ],
                    'help_text' => 'Add this product by default when creating new Order.'
                ])
            </div>
        </div>
    </div>
</div>