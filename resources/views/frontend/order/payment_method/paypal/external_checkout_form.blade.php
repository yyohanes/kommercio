@section('paypal_external_checkout_form')
    <p>
        {!! trans(
            LanguageHelper::getTranslationKey('order.payment_method.paypal.redirecting_to_paypal'),
            [
                'redirect_url' => $redirectUrl,
            ]
        ) !!}
    </p>

    <script type="text/javascript">
        var redirectUrl = '{{ $redirectUrl }}';

        if (!redirectUrl) {
            // If redirect link is empty, redirect to checkout page
            window.location.href = '{{ $redirectBackUrl }}';
        } else {
            setTimeout(function() {
                window.location.href = redirectUrl;
            }, 3000);
        }
    </script>
@show
