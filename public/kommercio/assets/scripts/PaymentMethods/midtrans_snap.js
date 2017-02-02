var KommercioMidtransSnap = function(){
  return {
    init: function(order_id, $form, options){
      if(typeof KommercioFrontend.runtimeObjects.checkoutForm != 'undefined'){
        var blocked = true;

        if($form.find('input[name="shipping_method"]:checked').length > 0){
          var $placeOrderBtn = $form.find('[name="process"][value="place_order"]');

          $placeOrderBtn.on('click', function(e){
            e.preventDefault();

            KommercioFrontend.toggleOverlay($form, true);

            if(blocked){
              $.ajax(options.getToken, {
                method: 'POST',
                data: {
                  'order_id': order_id,
                  '_token': global_vars.csrf_token
                },
                success: function(data){
                  snap.pay(data.token, {
                    onSuccess: function(result){
                      blocked = false;
                      $placeOrderBtn.trigger('click');
                    },
                    onPending: function(result){
                      blocked = false;

                      if(result.fraud_status != 'accept'){
                        alert(options.errorMessage);
                      }else{
                        $placeOrderBtn.trigger('click');
                      }
                    },
                    onError: function(result){
                      alert(options.errorMessage);
                    },
                    onClose: function(){
                      console.log('customer closed the popup without finishing the payment');
                    }
                  });
                },
                error: function(xhr){
                  alert(xhr.responseJSON.message);
                },
                complete: function(){
                  KommercioFrontend.toggleOverlay(KommercioFrontend.runtimeObjects.checkoutForm, false);
                }
              });
            }
          });
        }
      }
    }
  }
}();