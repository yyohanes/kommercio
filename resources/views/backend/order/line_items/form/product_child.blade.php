<tr class="child-line-item line-item" data-line_item="product" data-parent_product="{{ $parent->id }}" data-parent_line_item_key="{{ $parentKey }}" data-line_item_key="{{ $childKey }}" data-composite="{{ $composite->id }}">
    <td>
        @if(!$composite->isSingle)
        {!! Form::hidden('line_items['.$parentKey.'][children]['.$composite->id.']['.$childKey.'][product_composite_id]', $composite->id, ['class' => 'composite-id']) !!}
        {!! Form::hidden('line_items['.$parentKey.'][children]['.$composite->id.']['.$childKey.'][line_item_id]', null, ['class' => 'line-item-id']) !!}
        {!! Form::hidden('line_items['.$parentKey.'][children]['.$composite->id.']['.$childKey.'][line_item_type]', 'product') !!}
        @include('backend.master.form.fields.text', [
            'name' => 'line_items['.$parentKey.'][children]['.$composite->id.']['.$childKey.'][sku]',
            'label' => FALSE,
            'key' => 'line_items.'.$parentKey.'.children.'.$composite->id.'.'.$childKey.'.sku',
            'attr' => [
                'class' => 'form-control input-sm product-search',
                'id' => 'line_items['.$parentKey.'][children]['.$composite->id.']['.$childKey.'][sku]',
                'placeholder' => 'Search by SKU/Name',
                'data-typeahead_remote' => route('backend.catalog.product.composite.autocomplete', ['id' => $parent->id, 'composite_id' => $composite->id]),
                'data-typeahead_display' => 'sku',
                'data-typeahead_label'=> 'name',
                'data-isParent' => false
            ],
            'required' => TRUE,
            'defaultValue' => !empty($product)?$product->sku:null
        ])
        @else
        {{ $composite->product->name }}
        @endif

        @if(!empty($product) && ProjectHelper::isFeatureEnabled('catalog.product_configuration'))
            @foreach($product->productConfigurations as $productConfiguration)
                @include('backend.order.line_items.form.product_configuration.'.$productConfiguration->type, ['key' => $parentKey])
            @endforeach
        @endif
    </td>
    <td class="availability">
        <div class="order-limit-info">Limit: <span class="ordered-total">0</span>/<span class="limit-total">0</span></div>
        <div class="stock-info">Stock: <span class="stock-total">0</span></div>
    </td>
    <td>
        @if(!$composite->free)
        @include('backend.master.form.fields.number', [
            'name' => 'line_items['.$parentKey.'][children]['.$composite->id.']['.$childKey.'][net_price]',
            'label' => FALSE,
            'key' => 'line_items.'.$parentKey.'.children.'.$composite->id.'.'.$childKey.'.net_price',
            'attr' => [
                'class' => 'form-control input-sm net-price-field',
                'id' => 'line_items['.$parentKey.'][children]['.$composite->id.']['.$childKey.'][net_price]',
            ],
            'required' => TRUE,
            'unitPosition' => 'front',
            'unit' => CurrencyHelper::getCurrentCurrency()['symbol'],
            'defaultValue' => isset($product)?$product->getNetPrice():null
        ])
        @endif
    </td>
    <td>
        @if(!$composite->isSingle)
        @include('backend.master.form.fields.number', [
            'name' => 'line_items['.$parentKey.'][children]['.$composite->id.']['.$childKey.'][quantity]',
            'label' => FALSE,
            'key' => 'line_items.'.$parentKey.'.children.'.$composite->id.'.'.$childKey.'.quantity',
            'attr' => [
                'class' => 'form-control input-sm quantity-field',
                'id' => 'line_items['.$parentKey.'][children]['.$composite->id.']['.$childKey.'][quantity]'
            ],
            'required' => TRUE,
            'defaultValue' => 1
        ])
        @else
            {{ $composite->minimum + 0 }}
        @endif
    </td>
    <td>
        @if(!$composite->free)
        @include('backend.master.form.fields.number', [
            'name' => 'line_items['.$parentKey.'][children]['.$composite->id.']['.$childKey.'][lineitem_total_amount]',
            'label' => FALSE,
            'key' => 'line_items.'.$parentKey.'.children.'.$composite->id.'.'.$childKey.'.lineitem_total_amount',
            'attr' => [
                'class' => 'form-control input-sm lineitem-total-amount',
                'id' => 'line_items['.$parentKey.'][children]['.$composite->id.']['.$childKey.'][lineitem_total_amount]',
                'readonly' => TRUE
            ],
            'required' => TRUE,
            'unitPosition' => 'front',
            'unit' => CurrencyHelper::getCurrentCurrency()['symbol']
        ])
        @else
            {!! Form::hidden('line_items['.$parentKey.'][children]['.$composite->id.']['.$childKey.'][lineitem_total_amount]', 0, ['class' => 'net-price-field']) !!}
        @endif
    </td>
    <td class="text-center">
        @if(!$composite->isSingle)
        <a href="#" class="line-item-remove"><span class="text-danger"><i class="fa fa-remove"></i></span></a>
        @endif
    </td>
</tr>