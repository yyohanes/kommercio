<tr class="child-line-item" data-line_item="product" data-parent_product="{{ $parent->id }}" data-parent_line_item_key="{{ $parentKey }}" data-line_item_key="{{ $childKey }}" data-composite="{{ $composite->id }}">
    <td>
        @if(!$composite->pivot->isSingle)
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
        {{ $composite->pivot->configuredProduct->name }}
        @endif
    </td>
    <td class="availability">
        <!--
        <div class="order-limit-info">Limit: <span class="ordered-total">0</span>/<span class="limit-total">0</span></div>
        <div class="stock-info">Stock: <span class="stock-total">0</span></div>
        -->
    </td>
    <td>
        <!--
        @if(!$composite->pivot->isSingle)
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
            'defaultValue' => null
        ])
        @endif
        -->
    </td>
    <td>
        @if(!$composite->pivot->isSingle)
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
            {{ $composite->pivot->minimum + 0 }}
        @endif
    </td>
    <td>
        <!--
        @if(!$composite->pivot->isSingle)
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
        @endif
        -->
    </td>
    <td class="text-center">
        @if(!$composite->pivot->isSingle)
        <a href="#" class="line-item-remove"><span class="text-danger"><i class="fa fa-remove"></i></span></a>
        @endif
    </td>
</tr>