@if($type == \Kommercio\Models\Order\OrderLimit::TYPE_PRODUCT)
@include('backend.master.form.fields.select', [
    'name' => 'products[]',
    'label' => 'Products',
    'key' => 'products',
    'attr' => [
        'class' => 'form-control select2-ajax',
        'id' => 'products',
        'multiple' => TRUE,
        'data-remote_source' => $productSourceUrl,
        'data-remote_value_property' => 'sku',
        'data-remote_label_property' => 'name'
    ],
    'valueColumnClass' => 'col-md-6',
    'options' => $defaultProducts,
    'defaultOptions' => array_keys($defaultProducts),
    'help_text' => 'You can select more than one Product'
])
@endif

@include('backend.master.form.fields.select', [
    'name' => 'categories[]',
    'label' => 'Product Categories',
    'key' => 'categories',
    'attr' => [
        'class' => 'form-control select2-ajax',
        'id' => 'categories',
        'multiple' => TRUE,
        'data-remote_source' => $categorySourceUrl,
        'data-remote_value_property' => 'name',
        'data-remote_label_property' => 'name'
    ],
    'valueColumnClass' => 'col-md-6',
    'options' => $defaultProductCategories,
    'defaultOptions' => array_keys($defaultProductCategories),
    'help_text' => 'You can select more than one Product Category'
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

<div id="day-rules">
    <label class="control-label col-md-3">
        Every
    </label>

    <div class="col-md-9">
        <?php
        $days = ProjectHelper::getDaysOptions();
        ?>
        @foreach($dayRules as $idx => $dayRule)
            <div class="form-group">
                <div class="checkbox-list">
                    @foreach($days as $dayIdx => $day)
                        <label class="checkbox-inline">
                            {!! Form::checkbox('dayRules['.$idx.'][days][]', $dayIdx, is_object($dayRules)?$dayRule->$dayIdx:null) !!} {{ $day }} </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

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