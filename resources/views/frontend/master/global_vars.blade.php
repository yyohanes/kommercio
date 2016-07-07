<script type="text/javascript">
    var global_vars = {
        base_path: '{{ url('/') }}',
        images_path: '{{ config('kommercio.images_path') }}',
        asset_path: '{{ asset('/project/assets') }}',
        max_upload_size: {{ ProjectHelper::getMaxUploadSize() }},
        default_currency: '{{ CurrencyHelper::getCurrentCurrency()['code'] }}',
        currencies: {!! json_encode(CurrencyHelper::getActiveCurrencies()) !!},
        line_item_total_precision: {{ config('project.line_item_total_precision') }},
        total_precision: {{ config('project.total_precision') }},
        total_rounding: '{{ config('project.total_rounding') }}',
        csrf_token: '{{ csrf_token() }}'
    };
</script>