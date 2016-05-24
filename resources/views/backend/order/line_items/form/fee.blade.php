<tr class="line-item" data-line_item="fee" data-line_item_key="{{ $key }}">
    <td colspan="2">
        @include('backend.master.form.fields.text', [
            'name' => 'line_items[fees][sku]['.$key.']',
            'label' => FALSE,
            'key' => 'line_items.fees.sku.'.$key,
            'attr' => [
                'class' => 'form-control input-sm',
                'id' => 'line_items[fees][sku]['.$key.']',
                'placeholder' => 'Fee name',
            ],
            'required' => TRUE,
        ])
    </td>
    <td colspan="2"></td>
    <td>
        @include('backend.master.form.fields.number', [
            'name' => 'line_items[fees][lineitem_total_amount]['.$key.']',
            'label' => FALSE,
            'key' => 'line_items.fees.lineitem_total_amount.'.$key,
            'attr' => [
                'class' => 'form-control input-sm lineitem-total-amount',
                'id' => 'line_items[fees][lineitem_total_amount]['.$key.']',
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