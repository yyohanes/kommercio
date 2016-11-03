<?php
$index = isset($index)?$index:0;
?>
<div class="price-rule-option-group portlet">
    <div class="portlet-title">
        <div class="caption">
            <span> Option Group </span>
        </div>
        <div class="tools">
            <a href="" class="collapse" data-original-title="" title=""></a>
            <a href="" class="remove" data-original-title="" title=""> </a>
        </div>
    </div>
    <div class="portlet-body">
        <div class="col-md-6">
        @include('backend.master.form.fields.select', [
            'name' => 'options['.$index.'][categories][]',
            'label' => 'Categories',
            'key' => 'options.'.$index.'.categories',
            'attr' => [
                'class' => 'form-control multiselect',
                'multiple' => TRUE,
                'id' => 'options['.$index.'][categories][]',
            ],
            'two_lines' => TRUE,
            'options' => \Kommercio\Models\ProductCategory::getPossibleParentOptions(),
        ])
        </div>

        <div class="col-md-6">
        @include('backend.master.form.fields.select', [
            'name' => 'options['.$index.'][manufacturers][]',
            'label' => 'Manufacturers',
            'key' => 'options.'.$index.'.manufacturers',
            'attr' => [
                'class' => 'form-control multiselect',
                'multiple' => TRUE,
                'id' => 'options['.$index.'][manufacturers][]',
            ],
            'two_lines' => TRUE,
            'options' => \Kommercio\Models\Manufacturer::getOptions(),
        ])
        </div>

        <div class="col-md-6">
        @include('backend.master.form.fields.select', [
            'name' => 'options['.$index.'][attributeValues][]',
            'label' => 'Product Attributes',
            'key' => 'options.'.$index.'.attributeValues',
            'attr' => [
                'class' => 'form-control multiselect',
                'multiple' => TRUE,
                'id' => 'options['.$index.'][attributeValues][]',
            ],
            'two_lines' => TRUE,
            'options' => \Kommercio\Models\ProductAttribute\ProductAttribute::getProductAttributeWithValueOptions(),
        ])
        </div>

        @if(ProjectHelper::isFeatureEnabled('product_features'))
        <div class="col-md-6">
        @include('backend.master.form.fields.select', [
            'name' => 'options['.$index.'][featureValues][]',
            'label' => 'Product Features',
            'key' => 'options.'.$index.'.featureValues',
            'attr' => [
                'class' => 'form-control multiselect',
                'multiple' => TRUE,
                'id' => 'options['.$index.'][featureValues][]',
            ],
            'two_lines' => TRUE,
            'options' => \Kommercio\Models\ProductFeature\ProductFeature::getProductFeatureWithValueOptions(),
        ])
        </div>
        @endif

        <div class="clearfix"></div>

        {!! Form::hidden('price_rule_option_groups['.$index.']') !!}
    </div>
</div>