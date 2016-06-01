@include('backend.master.form.fields.text', [
    'name' => 'name',
    'label' => 'Name',
    'key' => 'name',
    'attr' => [
        'class' => 'form-control',
        'id' => 'name'
    ],
    'required' => TRUE
])

@include('backend.master.form.fields.number', [
    'name' => 'rate',
    'label' => 'Rate',
    'key' => 'rate',
    'attr' => [
        'class' => 'form-control',
        'id' => 'rate',
    ],
    'unit' => '%',
    'valueColumnClass' => 'col-md-4',
    'unitPosition' => 'behind',
    'required' => TRUE
])

@include('backend.master.form.fields.checkbox', [
    'name' => 'active',
    'label' => 'Active',
    'key' => 'active',
    'value' => 1,
    'attr' => [
        'class' => 'make-switch',
        'id' => 'active',
        'data-on-color' => 'warning'
    ],
    'checked' => $tax->active,
])

@include('backend.master.form.fields.select', [
    'name' => 'currency',
    'label' => 'Currency',
    'key' => 'currency',
    'attr' => [
        'class' => 'form-control',
        'id' => 'currency',
    ],
    'options' => $currencyOptions,
    'valueColumnClass' => 'col-md-4',
])

@include('backend.master.form.fields.select', [
    'name' => 'store_id',
    'label' => 'Store',
    'key' => 'store_id',
    'attr' => [
        'class' => 'form-control',
        'id' => 'store_id',
    ],
    'options' => $storeOptions,
    'valueColumnClass' => 'col-md-4',
])

@include('backend.master.form.fields.select', [
     'name' => 'country',
     'label' => 'Country',
     'key' => 'country',
     'attr' => [
         'class' => 'form-control',
         'id' => 'country',
         'data-country_children' => route('backend.tax.country_children')
     ],
     'options' => $countryOptions,
     'defaultOptions' => $country_id,
     'valueColumnClass' => 'col-md-4',
 ])

<div class="form-group">
    <label class="col-md-3">&nbsp;</label>
    <div class="col-md-9">
        <div id="country-children-wrapper" class="row">
            @include('backend.tax.country_children')
        </div>
    </div>
</div>

@section('bottom_page_scripts')
    @parent

    <script src="{{ asset('backend/assets/scripts/pages/tax_form.js') }}" type="text/javascript"></script>
@stop