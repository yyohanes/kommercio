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
    }

    plugin.initComponent = function(context)
    {
      $element.on('submit', function(){
        $element.triggerHandler('submit_payment', [invoiceFormData]);

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