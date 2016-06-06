<?php
switch($type){
    case \Kommercio\Models\Order\OrderLimit::TYPE_PRODUCT_CATEGORY:
        $sourceUrl = route('backend.catalog.category.autocomplete');
        $valueProperty = 'name';
        break;
    default:
        $sourceUrl = route('backend.catalog.product.autocomplete');
        $valueProperty = 'sku';
        break;

}
?>
@include('backend.master.form.fields.select', [
    'name' => 'items[]',
    'label' => str_plural(\Kommercio\Models\Order\OrderLimit::getTypeOptions($type)),
    'key' => 'items',
    'attr' => [
        'class' => 'form-control select2-ajax',
        'id' => 'items',
        'multiple' => TRUE,
        'data-remote_source' => $sourceUrl,
        'data-remote_value_property' => $valueProperty,
        'data-remote_label_property' => 'name'
    ],
    'required' => TRUE,
    'valueColumnClass' => 'col-md-6',
    'options' => $defaultItems,
    'defaultOptions' => array_keys($defaultItems),
    'help_text' => 'You can select more than one '.\Kommercio\Models\Order\OrderLimit::getTypeOptions($type)
])

@include('backend.master.form.fields.select', [
    'name' => 'limit_type',
    'label' => 'Limit Type',
    'key' => 'limit_type',
    'attr' => [
        'class' => 'form-control select2',
        'id' => 'limit_type',
    ],
    'required' => TRUE,
    'valueColumnClass' => 'col-md-4',
    'options' => \Kommercio\Models\Order\OrderLimit::getLimitTypeOptions(),
])

@include('backend.master.form.fields.number', [
    'name' => 'limit',
    'label' => 'Limit',
    'key' => 'limit',
    'attr' => [
        'class' => 'form-control',
        'id' => 'limit',
    ],
    'unit' => null,
    'required' => true,
    'valueColumnClass' => 'col-md-4',
    'unitPosition' => 'front'
])

@include('backend.master.form.fields.select', [
    'name' => 'store_id',
    'label' => 'Store',
    'key' => 'store_id',
    'attr' => [
        'class' => 'form-control select2',
        'id' => 'store_id',
    ],
    'options' => $storeOptions,
    'valueColumnClass' => 'col-md-4',
])

@include('backend.master.form.fields.datetime', [
    'name' => 'date_from',
    'label' => 'Date From',
    'key' => 'date_from',
    'attr' => [
        'id' => 'date_from'
    ],
    'valueColumnClass' => 'col-md-4'
])

@include('backend.master.form.fields.datetime', [
    'name' => 'date_to',
    'label' => 'Date To',
    'key' => 'date_to',
    'attr' => [
        'id' => 'date_to'
    ],
    'valueColumnClass' => 'col-md-4'
])

@include('backend.master.form.fields.checkbox', [
    'name' => 'active',
    'label' => 'Active',
    'key' => 'active',
    'value' => 1,
    'checked' => $orderLimit->exists?$orderLimit->active:true,
    'attr' => [
        'class' => 'make-switch',
        'id' => 'active',
        'data-on-color' => 'warning'
    ],
])

{!! Form::hidden('type', $type) !!}