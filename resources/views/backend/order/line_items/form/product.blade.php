<tr class="line-item" data-line_item="product">
    <td>
        @include('backend.master.form.fields.text', [
            'name' => 'line_items[sku][0]',
            'label' => FALSE,
            'key' => 'line_items.sku.0',
            'attr' => [
                'class' => 'form-control input-sm product-search',
                'id' => 'line_items[sku][0]',
                'placeholder' => 'Search by SKU/Name',
                'data-typeahead_remote' => route('backend.catalog.product.autocomplete'),
                'data-typeahead_display' => 'sku',
                'data-typeahead_label'=> 'name',
            ],
            'required' => TRUE,
            'defaultValue' => isset($product)?$product->sku:null
        ])
    </td>
    <td>
        {{ isset($product)?PriceFormatter::formatNumber($product->getRetailPrice()):null }}
    </td>
    <td>
        @include('backend.master.form.fields.number', [
            'name' => 'line_items[net_price][0]',
            'label' => FALSE,
            'key' => 'line_items.net_price.0',
            'attr' => [
                'class' => 'form-control input-sm',
                'id' => 'line_items[net_price][0]'
            ],
            'required' => TRUE,
            'unitPosition' => 'front',
            'unit' => CurrencyHelper::getCurrentCurrency()['symbol']
        ])
    </td>
    <td>
        @include('backend.master.form.fields.text', [
            'name' => 'line_items[quantity][0]',
            'label' => FALSE,
            'key' => 'line_items.quantity.0',
            'attr' => [
                'class' => 'form-control input-sm',
                'id' => 'line_items[quantity][0]'
            ],
            'required' => TRUE,
        ])
    </td>
    <td>

    </td>
    <td class="text-center">
        <a href="#" class="line-item-remove"><span class="text-danger"><i class="fa fa-remove"></i></span></a>
    </td>
</tr>