var KommercioPaypalExpressCheckout = function(){
  return {
    init: function(order_id, $form, options){
      if(typeof KommercioFrontend.runtimeObjects.checkoutForm != 'undefined' || typeof KommercioFrontend.runtimeObjects.invoiceForm != 'undefined'){
        if($form.find('input[name="shipping_method"]:checked').length > 0 || options.location == 'invoice'){
          var $placeOrderBtn = $form.find('[name="process"][value="pay"], [name="process"][value="place_order"]');

          if($('#paypal-btn').length == 0){
            $placeOrderBtn.after('<div id="paypal-btn"></div>');
          }

          paypal.Button.render({

            env: options.environment,

            style: {
              size: 'medium',
              color: 'gold',
              shape: 'pill'
            },

            payment: function(resolve, reject) {
              var CREATE_PAYMENT_URL = options.createPaymentUrl;

              KommercioFrontend.toggleOverlay($form, true);

              paypal.request.post(CREATE_PAYMENT_URL, {
                'order_id': order_id,
                '_token': global_vars.csrf_token
              })
                  .then(function(data) {
                    resolve(data.paymentID);
                  })
                  .catch(function(err) {
                    reject(err);
                    console.log(err);
                    KommercioFrontend.toggleOverlay($form, false);
                  });
            },

            onAuthorize: function(data) {
              KommercioFrontend.toggleOverlay($form, true);

              paypal.request.post(data.returnUrl,
                  {
                    'order_id': order_id,
                    '_token': global_vars.csrf_token
                  })

                  .then(function(data) {
                    console.log('success');
                    console.log(data);
                    $placeOrderBtn.click();
                  })
                  .catch(function(err) {
                    console.log(err);
                    KommercioFrontend.toggleOverlay($form, false);
                  });
            },
            onCancel: function(data, actions) {
              console.log(data);
              console.log(actions);
              alert('Payment failed. Please try again or choose another payment method.');
              KommercioFrontend.toggleOverlay($form, false);
            }

          }, '#paypal-btn');
        }
      }
    }
  }
}();