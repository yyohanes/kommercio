<tr class="line-item" data-line_item="shipping" data-line_item_key="{{ $key }}">
    <td>
        {!! Form::hidden('line_items['.$key.'][line_item_type]', 'shipping') !!}
        {!! Form::hidden('line_items['.$key.'][shipping_method_id]', null, ['class' => 'shipping-method-hidden']) !!}
        @include('backend.master.form.fields.text', [
            'name' => 'line_items['.$key.'][name]',
            'label' => FALSE,
            'key' => 'line_items.'.$key.'.name',
            'attr' => [
                'class' => 'form-control name-field input-sm',
                'id' => 'line_items['.$key.'][name]',
                'readonly' => TRUE
            ],
            'required' => TRUE,
        ])
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
        ])
    </td>
    <td colspan="2">

    </td>
    <td>
        @include('backend.master.form.fields.number', [
            'name' => 'line_items['.$key.'][lineitem_total_amount]',
            'label' => FALSE,
            'key' => 'line_items.lineitem_total_amount.'.$key,
            'attr' => [
                'class' => 'form-control input-sm lineitem-total-amount',
                'id' => 'line_items['.$key.'][lineitem_total_amount]',
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