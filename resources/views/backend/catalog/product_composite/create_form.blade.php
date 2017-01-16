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
    'name' => 'minimum',
    'label' => 'Minimum',
    'key' => 'minimum',
    'attr' => [
        'class' => 'form-control',
        'id' => 'minimum',
    ],
    'unitPosition' => 'front',
    'defaultValue' => old('minimum', $composite->exists?$composite->pivot->minimum:null),
    'required' => TRUE,
])

@include('backend.master.form.fields.number', [
    'name' => 'maximum',
    'label' => 'Maximum',
    'key' => 'maximum',
    'attr' => [
        'class' => 'form-control',
        'id' => 'maximum',
    ],
    'unitPosition' => 'front',
    'defaultValue' => old('maximum', $composite->exists?$composite->pivot->maximum:null),
    'required' => TRUE,
])

<div class="form-group composite-configuration-products">
    <label class="control-label col-md-3">
        Products
    </label>

    <div class="col-md-9">
        @include('backend.master.form.fields.text', [
            'name' => 'find_product',
            'label' => false,
            'key' => 'find_product',
            'attr' => [
                'data-product_relation_type' => 'composite',
                'class' => 'form-control product-configuration-finder',
                'placeholder' => 'Find product name...',
                'data-typeahead_remote' => route('backend.catalog.product.autocomplete'),
                'data-typeahead_display' => 'sku',
                'data-typeahead_label' => 'name',
                'data-typeahead_additional_query' => '&entity_only=1'
            ],
        ])

        <div class="configuration-products">
            @foreach(old('product', $composite->exists?$composite->products->pluck('id')->all():[]) as $compositeProductId)
                <?php $compositeProduct = \Kommercio\Models\Product::findOrFail($compositeProductId); ?>
                @include('backend.catalog.product.product_relation_result', ['product' => $compositeProduct, 'relation' => 'composite'])
            @endforeach
        </div>
    </div>
</div>

@include('backend.master.form.fields.select', [
    'name' => 'product_category[]',
    'label' => 'Categories',
    'key' => 'product_category',
    'attr' => [
        'class' => 'form-control multiselect',
        'multiple' => TRUE,
        'id' => 'product_category[]',
    ],
    'options' => $productCategoryOptions,
    'defaultOptions' => $composite->productCategories->pluck('id')->all()
])

@section('bottom_page_scripts')
    @parent

    <script>
        global_vars.get_related_product = '{{ route('backend.catalog.product.get_related') }}';
    </script>

    <script src="{{ asset('backend/assets/scripts/pages/product_composite_form.js') }}" type="text/javascript"></script>
@stop