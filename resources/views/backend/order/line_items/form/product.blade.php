<tr class="line-item" data-line_item="product" data-line_item_key="{{ $key }}">
    <td>
        @include('backend.master.form.fields.text', [
            'name' => 'line_items[products][sku]['.$key.']',
            'label' => FALSE,
            'key' => 'line_items.products.sku.'.$key,
            'attr' => [
                'class' => 'form-control input-sm product-search',
                'id' => 'line_items[products][sku]['.$key.']',
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
        @include('backend.master.form.fields.number', [
            'name' => 'line_items[products][retail_price]['.$key.']',
            'label' => FALSE,
            'key' => 'line_items.products.retail_price.'.$key,
            'attr' => [
                'class' => 'form-control input-sm retail-price-field',
                'id' => 'line_items[products][retail_price]['.$key.']',
                'disabled' => TRUE
            ],
            'required' => TRUE,
            'unitPosition' => 'front',
            'unit' => CurrencyHelper::getCurrentCurrency()['symbol'],
            'defaultValue' => isset($product)?$product->getRetailPrice():null
        ])
    </td>
    <td>
        @include('backend.master.form.fields.number', [
            'name' => 'line_items[products][net_price]['.$key.']',
            'label' => FALSE,
            'key' => 'line_items.products.net_price.'.$key,
            'attr' => [
                'class' => 'form-control input-sm net-price-field',
                'id' => 'line_items[products][net_price]['.$key.']'
            ],
            'required' => TRUE,
            'unitPosition' => 'front',
            'unit' => CurrencyHelper::getCurrentCurrency()['symbol'],
            'defaultValue' => isset($product)?$product->getNetPrice():null
        ])
    </td>
    <td>
        @include('backend.master.form.fields.text', [
            'name' => 'line_items[products][quantity]['.$key.']',
            'label' => FALSE,
            'key' => 'line_items.products.quantity.'.$key,
            'attr' => [
                'class' => 'form-control input-sm quantity-field',
                'id' => 'line_items[products][quantity]['.$key.']'
            ],
            'required' => TRUE,
            'defaultValue' => 1
        ])
    </td>
    <td>
        @include('backend.master.form.fields.number', [
            'name' => 'line_items[products][lineitem_total_amount]['.$key.']',
            'label' => FALSE,
            'key' => 'line_items.products.lineitem_total_amount.'.$key,
            'attr' => [
                'class' => 'form-control input-sm lineitem-total-amount',
                'id' => 'line_items[products][lineitem_total_amount]['.$key.']',
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