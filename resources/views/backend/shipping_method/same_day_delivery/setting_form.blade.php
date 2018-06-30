{!! Form::open(['class' => 'form-horizontal']) !!}
@include('backend.master.form.fields.textarea', [
    'name' => 'postal_settings',
    'label' => 'Postal Settings',
    'key' => 'postal_settings',
    'attr' => [
        'class' => 'form-control',
        'id' => 'postal_settings',
        'placeholder' => 'zone-1;^01[0-9]{4}$;60;6;8;300;0;30',
    ],
    'valueColumnClass' => 'col-md-6',
    'defaultValue' => old('postal_settings', $postalSettings),
    'help_text' => 'Format: Zone Name   ;   Postal (Regex)  ; Lead Time (Minute)    ;   Capacity (Hourly)    ;   Price   ;   Minimum Amount  ;   Maximum Amount  ;   Free Shipping Minimum Amount   ;   Limit',
])

@include('backend.master.form.fields.checkbox', [
    'name' => 'out_of_stock',
    'label' => 'Out of Stock',
    'key' => 'out_of_stock',
    'attr' => [
        'class' => 'make-switch',
        'id' => 'out_of_stock',
        'data-on-color' => 'warning',
        'data-size' => 'small',
    ],
    'value' => 1,
    'checked' => old('out_of_stock', $outOfStock)
])

{!! Form::hidden('shipping_method', $shippingMethod->id) !!}

<div class="form-actions text-center">
    <button class="btn btn-primary"><i class="fa fa-save"></i> Save </button>
    <button class="btn btn-link" href="{{ NavigationHelper::getBackUrl() }}"><i class="fa fa-remove"></i> Cancel </button>
</div>
{!! Form::close() !!}
