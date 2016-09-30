<script type="text/javascript">
    var global_vars = {
        base_path: '{{ url('/') }}',
        images_path: '{{ config('kommercio.images_path') }}',
        asset_path: '{{ asset('/project/assets') }}',
        max_upload_size: {{ ProjectHelper::getMaxUploadSize() }},
        default_currency: '{{ CurrencyHelper::getCurrentCurrency()['code'] }}',
        currencies: {!! json_encode(CurrencyHelper::getActiveCurrencies()) !!},
        line_item_total_precision: {{ ProjectHelper::getConfig('line_item_total_precision') }},
        total_precision: {{ ProjectHelper::getConfig('total_precision') }},
        total_rounding: '{{ ProjectHelper::getConfig('total_rounding') }}',
        csrf_token: '{{ csrf_token() }}',
        kommercio_api_url: '{{ KommercioAPIHelper::getAPIUrl() }}',
        enable_delivery_date: {{ ProjectHelper::getConfig('enable_delivery_date')?'true':'false' }},
        auth: {
            login_path: '{{ route('frontend.login_form') }}',
            logout_path: '{{ route('frontend.logout') }}'
        }
    };
</script>