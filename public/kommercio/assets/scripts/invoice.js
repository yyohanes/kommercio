(function($) {
  $.invoiceForm = function(element, options) {
    var defaults = {
      errorMessageClass: 'messages alert alert-danger'
    }

    var plugin = this;

    plugin.settings = {}

    var $element = $(element),
        element = element;

    plugin.init = function() {
      plugin.settings = $.extend({}, defaults, options);

      plugin.initComponent(element);
      KommercioFrontend.runtimeObjects.invoiceForm = $element;
      $element.trigger('invoice_form.initialized', [plugin]);
    }

    plugin.initComponent = function(context)
    {
      // Submit form with appended input[hidden][name="payment_method"]
      $element.find('[name="payment_method"]').on('change', function(){
        $element.append('<input name="change_payment_method" type="hidden" value="1" />');
        $element.submit();
      });

      $element.on('submit', function(){
        $element.trigger('submit_payment', [invoiceFormData]);

        KommercioFrontend.clearErrors(element);

        $element.append('<div class="loading-overlay" />');

        if(invoiceFormData.run_flag){
          return true;
        }else{
          invoiceFormData.run_flag = true;
          $element.find('.loading-overlay').remove();
          return false;
        }
      });
    }

    plugin.init();
  }

  var invoiceFormData = {
    run_flag: true
  };

  $.fn.invoiceForm = function(options) {

    return this.each(function() {
      if (undefined == $(this).data('invoiceForm')) {
        var plugin = new $.invoiceForm(this, options);
        $(this).data('invoiceForm', plugin);
      }
    });
  }

})(jQuery);