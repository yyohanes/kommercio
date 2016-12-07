<div id="reward-rule-{{ $type }}-wrapper" data-select_dependent="#type" data-select_dependent_value="{{ $type }}">
@include('backend.master.form.fields.number', [
    'name' => 'rule[order_step_amount]',
    'label' => 'Order Step Amount',
    'key' => 'rule.order_step_amount',
    'attr' => [
        'class' => 'form-control',
        'id' => 'order_step_amount',
        'data-currency_dependent' => '#currency',
        'data-number_type' => 'amount',
    ],
    'defaultValue' => old('rule.order_step_amount', $rewardRule->getData('rule.order_step_amount', null)),
    'unit' => CurrencyHelper::getCurrentCurrency()['symbol'],
    'unitPosition' => 'front',
    'help_text' => 'Amount that is counted as One rule',
    'valueColumnClass' => 'col-md-6',
])

@include('backend.master.form.fields.checkbox', [
    'name' => 'rule[include_shipping]',
    'label' => 'Include Shipping',
    'key' => 'rule.include_shipping',
    'value' => 1,
    'attr' => [
        'class' => 'make-switch',
        'id' => 'include_shipping',
        'data-on-color' => 'warning'
    ],
    'checked' => $rewardRule->getData('rule.include_shipping', false)
])

@include('backend.master.form.fields.checkbox', [
    'name' => 'rule[include_tax]',
    'label' => 'Include Tax',
    'key' => 'rule.include_tax',
    'value' => 1,
    'attr' => [
        'class' => 'make-switch',
        'id' => 'include_tax',
        'data-on-color' => 'warning'
    ],
    'checked' => $rewardRule->getData('rule.include_tax', false)
])
</div>