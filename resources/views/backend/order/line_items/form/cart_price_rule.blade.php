<div class="row static-info align-reverse cart-price-rule" data-cart_price_rule_id="{{ $cart_price_rule_id }}">
    <div class="col-md-8 name"> {{ $label }}: </div>
    <div class="col-md-4 value"> <span class="currency-symbol">{{ CurrencyHelper::getCurrentCurrency()['symbol'] }}</span> <span class="amount">{{ $value }}</span> </div>
    {!! Form::hidden('cart_price_rules['.(isset($idx)?$idx:null).']', $cart_price_rule_id) !!}
</div>