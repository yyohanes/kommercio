<div id="paypal-express-checkout-wrapper">
    @section('paypal_express_checkout_form')
    <!-- PayPal Logo -->
    <table border="0" cellpadding="10" cellspacing="0" align="center">
        <tr>
            <td align="center"></td>
        </tr>
        <tr>
            <td align="center">
                <a href="https://www.paypal.com/webapps/mpp/paypal-popup" title="How PayPal Works" onclick="javascript:window.open('https://www.paypal.com/webapps/mpp/paypal-popup','WIPaypal','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1060, height=700'); return false;">
                    <img src="https://www.paypalobjects.com/webstatic/mktg/logo/AM_mc_vs_dc_ae.jpg" border="0" alt="PayPal Acceptance Mark">
                </a>
            </td>
        </tr>
    </table>
    <!-- PayPal Logo -->
    @show
</div>

<script type="text/javascript">
    (function(){
        KommercioFrontend.loadJSScript('https://www.paypalobjects.com/api/checkout.js', function(){
            KommercioFrontend.loadJSScript('{{ asset('kommercio/assets/scripts/PaymentMethods/paypal_express_checkout.js') }}', function(){
                KommercioPaypalExpressCheckout.init(
                    '{{ $order->public_id }}',
                    $('#paypal-express-checkout-wrapper').parents('form'),
                    {
                        environment: '{{ $paymentMethod->getEnvironment() }}',
                        createPaymentUrl: '{{ route('frontend.payment_method.paypal.express_checkout.create') }}'
                    }
                );
            });
        });
    })();
</script>