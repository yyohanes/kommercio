<div id="stripe-wrapper">
@section('stripe_form')
    <span class="payment-errors"></span>

    <div class="form-row">
        <label>
            <span>Card Number</span>
            <input type="text" size="20" data-stripe="number">
        </label>
    </div>

    <div class="form-row">
        <label>
            <span>Expiration (MM/YY)</span>
            <input type="text" size="2" data-stripe="exp_month">
        </label>
        <span> / </span>
        <input type="text" size="2" data-stripe="exp_year">
    </div>

    <div class="form-row">
        <label>
            <span>CVC</span>
            <input type="text" size="4" data-stripe="cvc">
        </label>
    </div>
@show
    {!! Form::hidden('stripeToken', null, ['class' => 'stripeToken']) !!}
</div>

<script type="text/javascript">
    (function() {
        var stripeJS = document.createElement('script');
        stripeJS.src = 'https://js.stripe.com/v2/';
        stripeJS.type = 'text/javascript';
        stripeJS.async = 'true';
        stripeJS.onload = stripeJS.onreadystatechange = function() {
            var rs = this.readyState;
            if (rs && rs != 'complete' && rs != 'loaded') return;
            try {
                <?php
                $stripePaymentMethod = \Kommercio\Models\PaymentMethod\PaymentMethod::where('class', 'Stripe')->first();
                ?>
                Stripe.setPublishableKey('{{ $stripePaymentMethod->getData('publishable_key') }}');
            } catch (e) {}
        };
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(stripeJS, s);
    })();
</script>

<script type="text/javascript">
    (function() {
        var stripeCheckoutJS = document.createElement('script');
        stripeCheckoutJS.src = '{{ asset('kommercio/assets/scripts/PaymentMethods/stripe.js') }}';
        stripeCheckoutJS.type = 'text/javascript';
        stripeCheckoutJS.async = 'true';
        stripeCheckoutJS.onload = stripeCheckoutJS.onreadystatechange = function() {
            var rs = this.readyState;
            if (rs && rs != 'complete' && rs != 'loaded') return;
            try {
                KommercioStripe.init({{ $paymentMethod->id }}, $('#stripe-wrapper').parents('form'));
            } catch (e) {}
        };
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(stripeCheckoutJS, s);
    })();
</script>