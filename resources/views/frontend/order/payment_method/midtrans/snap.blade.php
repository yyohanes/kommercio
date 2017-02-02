<div id="midtrans-snap-wrapper">
    @section('midtrans_snap_form')
    @show
</div>

<script type="text/javascript">
    (function(){
        KommercioFrontend.loadJSScript('{{ $paymentMethod->getJsUrl() }}', function(){
            KommercioFrontend.loadJSScript('{{ asset('kommercio/assets/scripts/PaymentMethods/midtrans_snap.js') }}', function(){
                KommercioMidtransSnap.init(
                    '{{ $order->public_id }}',
                    $('#midtrans-snap-wrapper').parents('form'),
                    {
                        environment: '{{ $paymentMethod->getEnvironment() }}',
                        getToken: '{{ route('frontend.payment_method.midtrans.snap.token') }}',
                        errorMessage: '{{ LanguageHelper::getTranslationKey('frontend.checkout.payment.error') }}'
                    }
                );
            });
        });
    })();
</script>