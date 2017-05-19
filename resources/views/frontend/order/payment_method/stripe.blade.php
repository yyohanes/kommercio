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
  (function(){
    KommercioFrontend.loadJSScript('https://js.stripe.com/v2/', function(){
      try {
        <?php
        $stripePaymentMethod = \Kommercio\Models\PaymentMethod\PaymentMethod::where('class', 'Stripe')->first();
        ?>
        Stripe.setPublishableKey('{{ $stripePaymentMethod->getData('publishable_key') }}');

        KommercioFrontend.loadJSScript('{{ asset('kommercio/assets/scripts/PaymentMethods/stripe.js') }}', function(){
          KommercioStripe.init({{ $paymentMethod->id }}, $('#stripe-wrapper').parents('form'));
        });
      } catch (e) {}
    });
  })();
</script>