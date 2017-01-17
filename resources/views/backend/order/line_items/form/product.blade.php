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
                'data-isParent' => true
            ],
            'required' => TRUE,
            'defaultValue' => isset($product)?$product->sku:null
        ])

        @if(isset($product) && ProjectHelper::isFeatureEnabled('catalog.product_configuration'))
            @foreach($product->productConfigurations as $productConfiguration)
                @include('backend.order.line_items.form.product_configuration.'.$productConfiguration->type)
            @endforeach
        @endif

        @if(ProjectHelper::isFeatureEnabled('order.line_item_notes'))
        @include('backend.master.form.fields.textarea', [
            'name' => 'line_items['.$key.'][notes]',
            'label' => FALSE,
            'key' => 'line_items.'.$key.'.notes',
            'attr' => [
                'class' => 'form-control input-sm',
                'id' => 'line_items['.$key.'][notes]',
                'placeholder' => 'Notes',
                'rows' => 2
            ],
            'required' => TRUE,
        ])
        @endif
    </td>
    <td class="availability">
        <div class="order-limit-info">Limit: <span class="ordered-total">0</span>/<span class="limit-total">0</span></div>
        <div class="stock-info">Stock: <span class="stock-total">0</span></div>
    </td>
    <!--
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
    -->
    <td>
        @include('backend.master.form.fields.number', [
            'name' => 'line_items['.$key.'][net_price]',
            'label' => FALSE,
            'key' => 'line_items.'.$key.'.net_price',
            'attr' => [
                'class' => 'form-control input-sm net-price-field',
                'id' => 'line_items['.$key.'][net_price]',
            ],
            'required' => TRUE,
            'unitPosition' => 'front',
            'unit' => CurrencyHelper::getCurrentCurrency()['symbol'],
            'defaultValue' => isset($product)?$product->getNetPrice():null
        ])
    </td>
    <td>
        @include('backend.master.form.fields.number', [
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
            'key' => 'line_items.'.$key.'.lineitem_total_amount',
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

<?php
if(old('line_items.'.$key.'.children')){
    $product = \Kommercio\Models\Product::where('sku', old('line_items.'.$key.'.sku'))->firstOrFail();
}
?>

@if(!empty($product))
    @foreach($product->composites as $composite)
        <tr class="child-line-item-header" data-composite_id="{{ $composite->id }}" data-parent_line_item_key="{{ $key }}">
            <td colspan="100">
                {{ $composite->name }}
                @if($composite->products->count() > 1 && $composite->maximum > 1)
                <script id="lineitem-product-{{ $key }}-child-{{ $composite->id }}-template" type="text/x-handlebars-template">
                @include('backend.order.line_items.form.product_child', ['parentKey' => $key, 'childKey' => '@{{childKey}}', 'composite' => $composite, 'parent' => $product, 'product' => null])
                </script>
                <a href="#" class="configured-product-add btn btn-xs btn-default"><i class="fa fa-plus"></i></a>
                @endif
            </td>
        </tr>

        @foreach(old('line_items.'.$key.'.children.'.$composite->id, [0]) as $idx=>$child)
        @include('backend.order.line_items.form.product_child', ['parentKey' => $key, 'childKey' => $idx, 'composite' => $composite, 'parent' => $product, 'product' => null])
        @endforeach
    @endforeach
@endif