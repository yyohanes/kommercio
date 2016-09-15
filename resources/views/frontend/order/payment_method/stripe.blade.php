<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script type="text/javascript" src="{{ asset('kommercio/assets/scripts/stripe.js') }}"></script>
<script type="text/javascript">
    <?php
    $stripePaymentMethod = \Kommercio\Models\PaymentMethod\PaymentMethod::where('class', 'Stripe')->first();
    ?>
    Stripe.setPublishableKey('{{ $stripePaymentMethod->getData('api_token') }}');
</script>