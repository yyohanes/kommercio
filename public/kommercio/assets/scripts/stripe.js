var KommercioStripe = function(){
    return {
        init: function(form_selector){
            var $form = $(form_selector);

            var submitData = {
                number: $form.find('.card-number').val(),
                cvc: $form.find('.card-cvc').val(),
                exp_month: $form.find('.card-expiry-month').val(),
                exp_yearexp_month: $form.find('.card-expiry-year').val()
            };

            var stripeResponseHandler = function(status, response){
                if (response.error) {
                    // Show the errors on the form
                    $form.find('.payment-errors').text(response.error.message);

                } else {
                    // Get the token ID:
                    var token = response.id;

                    // Insert the token into the form so it gets submitted to the server:
                    $form.append($('<input type="hidden" name="stripeToken" />').val(token));
                }
            }

            Stripe.card.createToken(submitData, stripeResponseHandler);
        }
    }
}();