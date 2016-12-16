<div id="reward-{{ $type }}-wrapper" data-select_dependent="#type" data-select_dependent_value="{{ $type }}">
@include('backend.master.form.fields.select', [
    'name' => 'cart_price_rule_id',
    'label' => 'Cart Price Rule',
    'key' => 'cart_price_rule_id',
    'attr' => [
        'class' => 'form-control select2',
        'id' => 'cart_price_rule_id',
    ],
    'required' => TRUE,
    'options' => $cartPriceRuleOptions,
    'help_text' => 'Cart Price Rule to tie to this Reward',
    'valueColumnClass' => 'col-md-6',
])
</div>