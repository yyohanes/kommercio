var KommercioStripe = function(){
    return {
        init: function($paymentMethod, $form){
            var process;
            var formData;
            var paymentMethod = $paymentMethod;

            $form.on('validate_step_submit', function(e, $process, $data){
                if($process != 'select_payment_method' && $process != 'change'){
                    process = $process;
                    formData = $data;
                    formData.run_flag = false;

                    var submitData = {
                        number: $form.find('[data-stripe="number"]').val(),
                        cvc: $form.find('[data-stripe="cvc"]').val(),
                        exp_month: $form.find('[data-stripe="exp_month"]').val(),
                        exp_year: $form.find('[data-stripe="exp_year"]').val()
                    };

                    Stripe.card.createToken(submitData, stripeCheckoutResponseHandler);
                }
            });

            $form.on('submit_payment', function(e, $data){
              if($form.find('[name="payment_method"]:checked').val() == paymentMethod && $form.find('.stripeToken').val().length < 1){
                formData = $data;
                formData.run_flag = false;

                var submitData = {
                  number: $form.find('[data-stripe="number"]').val(),
                  cvc: $form.find('[data-stripe="cvc"]').val(),
                  exp_month: $form.find('[data-stripe="exp_month"]').val(),
                  exp_year: $form.find('[data-stripe="exp_year"]').val()
                };

                Stripe.card.createToken(submitData, stripeInvoiceResponseHandler);
              }
            });

            var stripeCheckoutResponseHandler = function(status, response){
                formData.run_flag = true;

                if (response.error) {
                    // Show the errors on the form
                    $form.find('.payment-errors').html('<div class="alert alert-danger">' + response.error.message + '</div>');
                } else {
                    // Get the token ID:
                    var token = response.id;

                    // Insert the token into the form so it gets submitted to the server:
                    $form.find('.stripeToken').remove();
                    $form.append($('<input type="hidden" class="stripeToken" name="stripeToken" />').val(token));

                    formData.plugin.submitCheckoutForm($form, process);
                }
            }

            var stripeInvoiceResponseHandler = function(status, response){
                formData.run_flag = true;

                if (response.error) {
                    // Show the errors on the form
                    $form.find('.payment-errors').html('<div class="alert alert-danger">' + response.error.message + '</div>');
                } else {
                    // Get the token ID:
                    var token = response.id;

                    // Insert the token into the form so it gets submitted to the server:
                    $form.find('.stripeToken').remove();
                    $form.append($('<input type="hidden" class="stripeToken" name="stripeToken" />').val(token));

                    $form.submit();
                }
            }
        }
    }
}();