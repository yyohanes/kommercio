(function($) {
  $.checkoutForm = function(element, options) {

    var defaults = {
      errorMessageClass: 'messages alert alert-danger',
      placeOrderBtn: '#place-order-btn'
    }

    var plugin = this;

    plugin.settings = {}

    var $element = $(element),
      element = element;

    plugin.init = function() {
      plugin.settings = $.extend({}, defaults, options);

      initComponent(element);
      KommercioFrontend.runtimeObjects.checkoutForm = $element;
      $element.trigger('checkout_form.initialized', [plugin]);
    }

    plugin.getPlaceOrderBtn = function(){
      return $(plugin.settings.placeOrderBtn);
    }

    plugin.processCheckout = function($form, $process)
    {
      KommercioFrontend.clearErrors(element);

      $form.triggerHandler('validate_step_submit', [$process, checkoutData]);

      KommercioFrontend.toggleOverlay($element, true);

      if(checkoutData.run_flag){
        plugin.submitCheckoutForm($form, $process);
      }else{
        checkoutData.run_flag = true;
        KommercioFrontend.toggleOverlay($element, false);
      }
    };

    plugin.submitCheckoutForm = function($form, $process){
      $.ajax($form.attr('action'), {
        method: 'POST',
        data: $form.serialize() + '&process=' + $process,
        success: function(data){
          var $html = null;

          for(var i in data.data){
            $html = $(data.data[i]);
            var $newForm = $html.filter('form');

            if(data.step == 'complete'){
              $('#ajax-meat').replaceWith($html);
            }else{
              $('#'+ i +'-wrapper', element).html($html);
            }

            initComponent($('#'+ i +'-wrapper', element));
            checkoutData.step = data.step;

            $newForm.trigger('after_step_change', [data.step, checkoutData.step, data]);
          }
        },
        complete: function(){
          KommercioFrontend.toggleOverlay($element, false);
        },
        error: function(data){
          $form.trigger('error', [checkoutData.step, data]);

          if($process != 'place_order'){
            for(var i in data.responseJSON){
              KommercioFrontend.addError(i, data.responseJSON[i][0], element);
            }
          }else{
            $('#checkout_summary-wrapper .messages', element).remove();
            $('#checkout_summary-wrapper .update-cart', element).prepend('<div class="' + plugin.settings.errorMessageClass + '"></div>');

            for(var i in data.responseJSON){
              $('#checkout_summary-information .messages', element).append('<div>' + data.responseJSON[i][0] + '</div>');
            }
          }
        }
      });
    }

    var initComponent = function(context)
    {
      handleAddressSelector(context);
      handleStepButton(context);
      handleOrderButton(context);
      handleShippingMethod(context);
      handlePaymentMethod(context);
      if(global_vars.enable_delivery_date){
        handleAvailability(context);
      }
      handleSavedAddress(context);
      handleForm(context);
    }

    var handleSavedAddress = function(context)
    {
        $('#shipping-profile-select', context).on('change', function(){
            var $form = $(this).parents('form');

            plugin.processCheckout($form, 'change');
        });
    }

    var handleStepButton = function(context)
    {
        $('.next-step-btn', context).each(function(idx, obj){
            $(obj).on('click', function(e){
                e.preventDefault();

                var $form = $(obj).parents('form');

                plugin.processCheckout($form, $(this).val());
            });
        });
    }

    var handleOrderButton = function(context)
    {
      $('[name="process"][value="place_order"]', context).on('click', function(e){
        var $form = $(e.target).parents('form');

        $form.triggerHandler('place_order', [checkoutData]);
        KommercioFrontend.toggleOverlay($element, true);
      });
    }

    var handleShippingMethod = function(context)
    {
      $('input[name="shipping_method"]', context).on('change', function(){
        var $form = $(this).parents('form');

        //if($form.attr('action').search('shipping_method') < 0){
          plugin.processCheckout($form, 'select_shipping_method');
        //}
      });
    }

    var handlePaymentMethod = function(context)
    {
      $('input[name="payment_method"]', context).on('change', function(){
        var $form = $(this).parents('form');

        plugin.processCheckout($form, 'select_payment_method');
      });
    }

    var $disabledDates = [];
    var $checkoutForm;

    var handleAvailability = function(context)
    {
      var datePickerClosed = true;
      var datepickerDate;
      $checkoutForm = $('#customer_information-wrapper form', context);

      //Availability from Calendar
      $datePicker = $('#delivery-datepicker', context);
      $datePicker.datepicker({
        startDate: global_vars.soonest_delivery_day,
        format: 'yyyy-mm-dd',
        autoclose: true,
        container: '#delivery-date-panel'
        /*,
         beforeShowDay: function(e){
         if($disabledDates.indexOf(e.getFullYear() + '-' + (e.getMonth()+1) + '-' + e.getDate()) > -1){
         return false;
         }
         }
         */
      }).on('show', function(e){
        if(datePickerClosed){
          datepickerDate = $(e.target).datepicker('getDate');

          if(datepickerDate == null){
            datepickerDate = new Date();
          }

          handleOnChangeMonth(e, datepickerDate.getMonth(), datepickerDate.getFullYear(), datepickerDate.getDate());
        }

        datePickerClosed = false;
      }).on('hide', function(e){
        datePickerClosed = true;
      }).on('changeMonth', function(e){
        handleOnChangeMonth(e, e.date.getMonth(), e.date.getFullYear());
      });
      //End Availability from Calendar
    }

    var handleOnChangeMonth = function(e, month, year, date)
    {
      $('#delivery-date-panel .datepicker-days').css('visibility', 'hidden');
      $('#delivery-date-panel .datepicker').addClass('loading');

      $.ajax(global_vars.get_availability_calendar, {
        method: 'POST',
        data: $checkoutForm.serialize() + '&month=' + (month+1) + '&year=' + year,
        success: function(data){
          $disabledDates = $.makeArray(data.disabled_dates);
          $(e.target).datepicker('setDatesDisabled', $disabledDates);
          $(e.target).datepicker('update', e.date);
        },
        complete: function(){
          $('#delivery-date-panel .datepicker-days').css('visibility', 'visible');
          $('#delivery-date-panel .datepicker').removeClass('loading');
        }
      });
    }

    var handleForm = function(context)
    {
      $('form', context).each(function(idx, obj){
        $(obj).data('checkoutForm', plugin);
        $(obj).trigger('checkout_form.form_initialized', [plugin]);
      });
    }

    var checkoutData = {
      run_flag: true,
      step: null,
      plugin: plugin
    };

    plugin.init();
  }

  $.fn.checkoutForm = function(options) {
    return this.each(function() {
      if (undefined == $(this).data('checkoutForm')) {
        var plugin = new $.checkoutForm(this, options);
        $(this).data('checkoutForm', plugin);
      }
    });
  }

})(jQuery);