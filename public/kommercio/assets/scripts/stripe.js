var KommercioStripe = function(){
    return {
        init: function($form){
            var process;
            var checkoutData;

            $form.on('validate_step_submit', function(e, $process, $data){
                if($process != 'select_payment_method' && $process != 'change'){
                    process = $process;
                    checkoutData = $data;
                    checkoutData.run_flag = false;

                    var submitData = {
                        number: $form.find('[data-stripe="number"]').val(),
                        cvc: $form.find('[data-stripe="cvc"]').val(),
                        exp_month: $form.find('[data-stripe="exp_month"]').val(),
                        exp_year: $form.find('[data-stripe="exp_year"]').val()
                    };

                    Stripe.card.createToken(submitData, stripeResponseHandler);
                }
            });

            var stripeResponseHandler = function(status, response){
                if (response.error) {
                    // Show the errors on the form
                    $form.find('.payment-errors').text(response.error.message);
                    checkoutData.run_flag = false;
                } else {
                    // Get the token ID:
                    var token = response.id;

                    // Insert the token into the form so it gets submitted to the server:
                    $form.find('.stripeToken').remove();
                    $form.append($('<input type="hidden" class="stripeToken" name="stripeToken" />').val(token));

                    checkoutData.run_flag = true;

                    checkoutForm.submitCheckoutForm($form, process);
                }
            }
        }
    }
}();