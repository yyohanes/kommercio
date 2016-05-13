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
        @include('backend.master.form.fields.select', [
            'name' => 'options['.$index.'][categories][]',
            'label' => 'Categories',
            'key' => 'options.'.$index.'.categories',
            'attr' => [
                'class' => 'form-control multiselect',
                'multiple' => TRUE,
                'id' => 'options['.$index.'][categories][]',
            ],
            'options' => \Kommercio\Models\ProductCategory::getPossibleParentOptions(),
        ])

        @include('backend.master.form.fields.select', [
            'name' => 'options['.$index.'][manufacturers][]',
            'label' => 'Manufacturers',
            'key' => 'options.'.$index.'.manufacturers',
            'attr' => [
                'class' => 'form-control multiselect',
                'multiple' => TRUE,
                'id' => 'options['.$index.'][manufacturers][]',
            ],
            'options' => \Kommercio\Models\Manufacturer::getOptions(),
        ])

        @include('backend.master.form.fields.select', [
            'name' => 'options['.$index.'][attributeValues][]',
            'label' => 'Product Attributes',
            'key' => 'options.'.$index.'.attributeValues',
            'attr' => [
                'class' => 'form-control multiselect',
                'multiple' => TRUE,
                'id' => 'options['.$index.'][attributeValues][]',
            ],
            'options' => \Kommercio\Models\ProductAttribute\ProductAttribute::getProductAttributeWithValueOptions(),
        ])

        @include('backend.master.form.fields.select', [
            'name' => 'options['.$index.'][featureValues][]',
            'label' => 'Product Features',
            'key' => 'options.'.$index.'.featureValues',
            'attr' => [
                'class' => 'form-control multiselect',
                'multiple' => TRUE,
                'id' => 'options['.$index.'][featureValues][]',
            ],
            'options' => \Kommercio\Models\ProductFeature\ProductFeature::getProductFeatureWithValueOptions(),
        ])

        {!! Form::hidden('price_rule_option_groups['.$index.']') !!}
    </div>
</div>