(function($) {

  $.productOrderForm = function(element, options) {

    var defaults = {}

    var plugin = this;

    plugin.settings = {
      data_variation_url: 'get_variation_url',
      detail_wrapper_selector: '#product-detail-wrapper',
      add_to_cart_selector: '.add-to-cart-btn'
    }

    var $element = $(element),
      element = element;

    plugin.init = function() {
      plugin.settings = $.extend({}, defaults, options);

      plugin.initComponent(element);
    }

    plugin.initComponent = function(context)
    {
      handleAttributeSelector();
      handleOrderForm();
    }

    var handleAttributeSelector = function(){
      $('.attribute-selector', element).on('change', function(e){
        $.ajax($element.data(plugin.settings.data_variation_url) + '?variation=' + $(this).val(), {
          method: 'GET',
          success: function(data){
            var $productDetail = $(plugin.settings.detail_wrapper_selector, data);

            $element.trigger('productOrderForm.variationLoaded', [$productDetail]);

            $(plugin.settings.detail_wrapper_selector).html($productDetail.html());
          }
        });
      });
    }

    var handleOrderForm = function(){
      $(plugin.settings.add_to_cart_selector, element).click(function(e){
        e.preventDefault();

        $.ajax(
          $element.attr('action'),
          {
            method: 'POST',
            data: $element.serialize(),
            success: function(data){
              if(data.success){
                $element.trigger('productOrderForm.addedToCart', [data.data]);
              }
            },
            error: function(xhr){
              for(var i in xhr.responseJSON){
                alert(xhr.responseJSON[i][0]);
                break;
              }
            }
          }
        );
      });
    }

    plugin.init();

  }

  $.fn.productOrderForm = function(options) {

    return this.each(function() {
      if (undefined == $(this).data('productOrderForm')) {
        var plugin = new $.productOrderForm(this, options);
        $(this).data('productOrderForm', plugin);
      }
    });

  }

})(jQuery);