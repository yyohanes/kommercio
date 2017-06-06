{!! Form::open(['class' => 'form-horizontal']) !!}
@include('backend.master.form.fields.number', [
    'name' => 'price',
    'label' => 'Shipping Cost',
    'key' => 'price',
    'attr' => [
        'class' => 'form-control',
        'id' => 'price',
    ],
    'unit' => CurrencyHelper::getCurrentCurrency()['symbol'],
    'valueColumnClass' => 'col-md-6',
    'unitPosition' => 'front',
    'defaultValue' => old('price', $rate?$rate->price:null),
])

{!! Form::hidden('shipping_method', $shippingMethod->id) !!}
{!! Form::hidden('currency', CurrencyHelper::getCurrentCurrency()['code']) !!}

<div class="form-actions text-center">
    <button class="btn btn-primary"><i class="fa fa-save"></i> Save </button>
    <button class="btn btn-link" href="{{ NavigationHelper::getBackUrl() }}"><i class="fa fa-remove"></i> Cancel </button>
</div>
{!! Form::close() !!}