<?php $taxable = isset($product)?$product->productDetail->taxable:false; ?>
<tr class="line-item" data-taxable="{{ old('line_items.'.$key.'.taxable', $taxable) }}" data-line_item="product" data-line_item_key="{{ $key }}">
    <td>
        {!! Form::hidden('line_items['.$key.'][line_item_id]', isset($product)?$product->id:null, ['class' => 'line-item-id']) !!}
        {!! Form::hidden('line_items['.$key.'][line_item_type]', 'product') !!}
        {!! Form::hidden('line_items['.$key.'][taxable]', $taxable) !!}
        @include('backend.master.form.fields.text', [
            'name' => 'line_items['.$key.'][sku]',
            'label' => FALSE,
            'key' => 'line_items.'.$key.'.sku',
            'attr' => [
                'class' => 'form-control input-sm product-search',
                'id' => 'line_items['.$key.'][sku]',
                'placeholder' => 'Search by SKU/Name',
                'data-typeahead_remote' => route('backend.catalog.product.autocomplete'),
                'data-typeahead_display' => 'sku',
                'data-typeahead_label'=> 'name',
            ],
            'required' => TRUE,
            'defaultValue' => isset($product)?$product->sku:null
        ])
    </td>
    <td class="availability">
        <div class="order-limit-info">Limit: <span class="ordered-total">0</span>/<span class="limit-total">0</span></div>
        <div class="stock-info">Stock: <span class="stock-total">0</span></div>
    </td>
    <td>
        @include('backend.master.form.fields.number', [
            'name' => 'line_items['.$key.'][base_price]',
            'label' => FALSE,
            'key' => 'line_items.'.$key.'.base_price',
            'attr' => [
                'class' => 'form-control input-sm base-price-field',
                'id' => 'line_items['.$key.'][base_price]',
                'readonly' => TRUE
            ],
            'required' => TRUE,
            'unitPosition' => 'front',
            'unit' => CurrencyHelper::getCurrentCurrency()['symbol'],
            'defaultValue' => isset($product)?$product->getRetailPrice():null
        ])
    </td>
    <td>
        @include('backend.master.form.fields.number', [
            'name' => 'line_items['.$key.'][net_price]',
            'label' => FALSE,
            'key' => 'line_items.'.$key.'.net_price',
            'attr' => [
                'class' => 'form-control input-sm net-price-field',
                'id' => 'line_items['.$key.'][net_price]'
            ],
            'required' => TRUE,
            'unitPosition' => 'front',
            'unit' => CurrencyHelper::getCurrentCurrency()['symbol'],
            'defaultValue' => isset($product)?$product->getNetPrice():null
        ])
    </td>
    <td>
        @include('backend.master.form.fields.text', [
            'name' => 'line_items['.$key.'][quantity]',
            'label' => FALSE,
            'key' => 'line_items.'.$key.'.quantity',
            'attr' => [
                'class' => 'form-control input-sm quantity-field',
                'id' => 'line_items['.$key.'][quantity]'
            ],
            'required' => TRUE,
            'defaultValue' => 1
        ])
    </td>
    <td>
        @include('backend.master.form.fields.number', [
            'name' => 'line_items['.$key.'][lineitem_total_amount]',
            'label' => FALSE,
            'key' => 'line_items.lineitem_total_amount.'.$key,
            'attr' => [
                'class' => 'form-control input-sm lineitem-total-amount',
                'id' => 'line_items['.$key.'][lineitem_total_amount]',
                'readonly' => TRUE
            ],
            'required' => TRUE,
            'unitPosition' => 'front',
            'unit' => CurrencyHelper::getCurrentCurrency()['symbol']
        ])
    </td>
    <td class="text-center">
        <a href="#" class="line-item-remove"><span class="text-danger"><i class="fa fa-remove"></i></span></a>
    </td>
</tr>