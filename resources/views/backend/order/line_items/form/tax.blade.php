<div class="row static-info align-reverse tax" data-tax_id="{{ $tax_id }}" data-tax_rate="{{ $rate }}">
    <div class="col-md-8 name"> {{ $label }}: </div>
    <div class="col-md-4 value"> <span class="currency-symbol">{{ CurrencyHelper::getCurrentCurrency()['symbol'] }}</span> <span class="amount">{{ $value }}</span> </div>
    {!! Form::hidden('taxes['.(isset($idx)?$idx:null).']', $tax_id) !!}
</div>