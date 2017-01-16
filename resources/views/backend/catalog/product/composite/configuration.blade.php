<?php
$index = isset($index)?$index:0;
?>
<div class="composite-configuration portlet">
    <div class="portlet-title">
        <div class="caption">
            <span> Configuration </span>
        </div>
        <div class="tools">
            <a href="" class="collapse" data-original-title="" title=""></a>
            <a href="" class="remove" data-original-title="" title=""> </a>
        </div>
    </div>
    <div class="portlet-body">
        @include('backend.master.form.fields.text', [
                'name' => 'compositeConfigurations['.$index.'][name]',
                'label' => 'Name',
                'key' => 'compositeConfigurations.'.$index.'.name',
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'name'
                ],
                'defaultValue' => old('compositeConfigurations.'.$index.'.name', is_object($compositeConfiguration)?$compositeConfiguration->name:null),
                'required' => TRUE
            ])

        @include('backend.master.form.fields.number', [
            'name' => 'compositeConfigurations['.$index.'][minimum]',
            'label' => 'Minimum',
            'key' => 'compositeConfigurations.'.$index.'.minimum',
            'attr' => [
                'class' => 'form-control',
                'id' => 'minimum',
            ],
            'unitPosition' => 'front',
            'defaultValue' => old('compositeConfigurations.'.$index.'.minimum', is_object($compositeConfiguration)?$compositeConfiguration->pivot->minimum:null),
            'required' => TRUE,
        ])

        @include('backend.master.form.fields.number', [
            'name' => 'compositeConfigurations['.$index.'][maximum]',
            'label' => 'Maximum',
            'key' => 'compositeConfigurations.'.$index.'.maximum',
            'attr' => [
                'class' => 'form-control',
                'id' => 'maximum',
            ],
            'unitPosition' => 'front',
            'defaultValue' => old('compositeConfigurations.'.$index.'.maximum', is_object($compositeConfiguration)?$compositeConfiguration->pivot->maximum:null),
            'required' => TRUE
        ])

        <div class="form-group composite-configuration-products">
            <label class="control-label col-md-3">
                Products <span class="required">*</span>
            </label>

            <div class="col-md-9">
                @include('backend.master.form.fields.text', [
                    'name' => 'find_product['.$index.']',
                    'label' => false,
                    'key' => 'find_product.'.$index,
                    'attr' => [
                        'data-product_relation_type' => 'composite_products_'.$index,
                        'class' => 'form-control product-configuration-finder',
                        'placeholder' => 'Find product name...',
                        'data-typeahead_remote' => route('backend.catalog.product.autocomplete'),
                        'data-typeahead_display' => 'sku',
                        'data-typeahead_label' => 'name',
                        'data-typeahead_additional_query' => '&entity_only=1&exclude='.$product->id
                    ],
                ])

                <div class="configuration-products">
                    @foreach(old('composite_products_'.$index.'_product', is_object($compositeConfiguration)?$compositeConfiguration->pivot->configuredProducts->pluck('id')->all():[]) as $compositeProductId)
                        <?php $compositeProduct = \Kommercio\Models\Product::findOrFail($compositeProductId); ?>
                        @include('backend.catalog.product.product_relation_result', ['product' => $compositeProduct, 'relation' => 'composite_products_'.$index])
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>