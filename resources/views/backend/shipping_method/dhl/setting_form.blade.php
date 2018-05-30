{!! Form::open(['class' => 'form-horizontal']) !!}
@include('backend.master.form.fields.select', [
    'name' => 'regionCode',
    'label' => 'Region Code',
    'key' => 'regionCode',
    'attr' => [
        'class' => 'form-control',
        'id' => 'regionCode',
    ],
    'options' => $regionCodeOptions,
    'valueColumnClass' => 'col-md-6',
    'defaultOptions' => old('regionCode', $regionCode),
])

@include('backend.master.form.fields.text', [
    'name' => 'fallbackCityName',
    'label' => 'Fallback City Name',
    'key' => 'fallbackCityName',
    'attr' => [
        'class' => 'form-control',
        'id' => 'fallbackCityName',
    ],
    'valueColumnClass' => 'col-md-6',
    'defaultValue' => old('fallbackCityName', $fallbackCityName),
])

@include('backend.master.form.fields.number', [
    'name' => 'dutiableMinimum',
    'label' => 'Dutiable Minimum',
    'key' => 'dutiableMinimum',
    'attr' => [
        'class' => 'form-control',
        'id' => 'dutiableMinimum',
    ],
    'unit' => CurrencyHelper::getCurrentCurrency()['symbol'],
    'valueColumnClass' => 'col-md-6',
    'unitPosition' => 'front',
    'defaultValue' => old('dutiableMinimum', $dutiableMinimum),
])

@include('backend.master.form.fields.select', [
    'name' => 'dutiableCurrency',
    'label' => 'Currency',
    'key' => 'dutiableCurrency',
    'attr' => [
        'class' => 'form-control',
        'id' => 'dutiableCurrency',
    ],
    'options' => $currencyOptions,
    'valueColumnClass' => 'col-md-4',
    'defaultOptions' => old('dutiableCurrency', $dutiableCurrency)
])

{!! Form::hidden('shipping_method', $shippingMethod->id) !!}

<div class="form-actions text-center">
    <button class="btn btn-primary"><i class="fa fa-save"></i> Save </button>
    <button class="btn btn-link" href="{{ NavigationHelper::getBackUrl() }}"><i class="fa fa-remove"></i> Cancel </button>
</div>
{!! Form::close() !!}

