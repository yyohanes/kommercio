<tr class="line-item" data-taxable="{{ $taxable }}" data-line_item="shipping" data-line_item_key="{{ $key }}">
    <td colspan="2">
        {!! Form::hidden('line_items['.$key.'][line_item_type]', 'shipping') !!}
        {!! Form::hidden('line_items['.$key.'][line_item_id]', $shipping_method_id) !!}
        {!! Form::hidden('line_items['.$key.'][shipping_method]', $shipping_method) !!}
        {!! Form::hidden('line_items['.$key.'][taxable]', $taxable) !!}
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
            ],
            'required' => TRUE,
            'unitPosition' => 'front',
            'unit' => CurrencyHelper::getCurrentCurrency()['symbol'],
        ])
    </td>
    <td>
        @include('backend.master.form.fields.number', [
            'name' => 'line_items['.$key.'][net_price]',
            'label' => FALSE,
            'key' => 'line_items.'.$key.'.net_price',
            'attr' => [
                'class' => 'form-control input-sm net-price-field',
                'id' => 'line_items['.$key.'][net_price]',
                'readonly' => TRUE
            ],
            'required' => TRUE,
            'unitPosition' => 'front',
            'unit' => CurrencyHelper::getCurrentCurrency()['symbol'],
        ])
    </td>
    <td></td>
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
        {!! Form::hidden('line_items['.$key.'][quantity]', 1, ['class' => 'quantity-field']) !!}
        <a href="#" class="line-item-remove"><span class="text-danger"><i class="fa fa-remove"></i></span></a>
    </td>
</tr>