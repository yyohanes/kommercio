<script type="text/javascript">
    var global_vars = {
        project_name: '{{ ProjectHelper::getConfig('project_machine_name') }}',
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
        },
        cart_clear_path: '{{ route('frontend.order.cart.clear') }}',
        mini_cart_path: '{{ route('frontend.order.cart.mini') }}',
        @if(config('project.enable_delivery_date'))
        soonest_delivery_day: new Date('{{ FrontendHelper::getSoonestDeliveryDay() }}'),
        @endif
        get_availability_calendar: '{{ route('catalog.product.availability_calendar') }}'
};
</script>

<script type="text/javascript" src="{{ asset('kommercio/assets/scripts/app.js') }}"></script>