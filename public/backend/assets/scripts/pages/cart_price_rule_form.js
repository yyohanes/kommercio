var cartPriceRuleForm = function (){
  var handleCouponForm = function()
  {
    $('#add-coupon-btn').on('click', function(e){
      e.preventDefault();

      cartPriceRuleFormBehaviors.loadForm('?new_form', $(this).data('form'));
    });
  }

    var newPriceRuleOptionGroupCount = $('#price-rule-option-groups-wrapper .price-rule-option-group').length;

    processNewPriceRuleOptionGroup = function(){
        newPriceRuleOptionGroupCount += 1;
        var $newPriceRuleOptionGroup = $($priceRuleOptionGroupMockup);

        $newPriceRuleOptionGroup.find('[name]').each(function(idx, obj){
            $(obj).attr('name', $(obj).attr('name').replace('[0]', '['+newPriceRuleOptionGroupCount+']'));

            var $attr = $(obj).attr('id');

            if (typeof $attr !== typeof undefined && $attr !== false) {
                var $newId = $attr.replace('[0]', '['+newPriceRuleOptionGroupCount+']');

                $newPriceRuleOptionGroup.find('label[for="'+$attr+'"]').attr('for', $newId);
                $(obj).attr('id', $newId);
            }
        });

        formBehaviors.init($newPriceRuleOptionGroup);

        $newPriceRuleOptionGroup.appendTo('#price-rule-option-groups-wrapper');
        App.scrollTo($newPriceRuleOptionGroup, 1);
    }

    handlePriceRuleOptions = function(){
        $('#price-rule-options-add').click(function(e){
            e.preventDefault();

            processNewPriceRuleOptionGroup();
        });
    }

    return {
      init: function(){
        handlePriceRuleOptions();
        handleCouponForm();

        cartPriceRuleFormBehaviors.handleEditCoupon('#coupon-wrapper');

        $(document).ajaxComplete(function( event,request, settings ) {
          App.unblockUI('#coupon-section');
        });
      }
    }
}();

jQuery(document).ready(function() {
    cartPriceRuleForm.init();
});

var cartPriceRuleFormBehaviors = {
  initAjax: function(context){
    $('#coupon-save', context).click(function(e){
      e.preventDefault();

      App.blockUI({
        target: '#coupon-section',
        boxed: true,
        message: 'Saving coupon...'
      });

      formHelper.clearFormError({
        'wrapper': '#coupon-form-wrapper'
      });

      $.ajax($(this).data('coupon_save'), {
        'method': 'POST',
        'data': $('#coupon-form-wrapper :input').serialize(),
        'success': function(data){
          if(data.result == 'success'){
            $.bootstrapGrowl(data.message, {
              ele: 'body', // which element to append to
              type: 'success', // (null, 'info', 'danger', 'success')
              offset: {from: 'top', amount: 20}, // 'top', or 'bottom'
              align: 'right', // ('left', 'right', or 'center')
              width: 250, // (integer, or 'auto')
              delay: 4000, // Time while the message will be displayed. It's not equivalent to the *demo* timeOut!
              allow_dismiss: true, // If true then will display a cross to close the popup.
              stackup_spacing: 10 // spacing between consecutively stacked growls.
            });

            cartPriceRuleFormBehaviors.closeForm();
            cartPriceRuleFormBehaviors.refreshIndex();
          }
        },
        'error': function(xhr){
          for(var i in xhr.responseJSON){
            var $errorName = formHelper.convertDotToSquareBracket(i);
            formHelper.addFieldError({
              'name': $errorName,
              'message': xhr.responseJSON[i][0],
              'context': '#coupon-form-wrapper',
              'messagesWrapper' : '#coupon-messages'
            });
          }

          App.scrollTo($('#coupon-form-wrapper'));

          formBehaviors.initComponents(context);
        }
      });
    });

    $('#coupon-cancel', context).on('click', function(e){
      e.preventDefault();

      cartPriceRuleFormBehaviors.closeForm();
    });
  },
  handleEditCoupon: function(context){
    $('.coupon-edit-btn', context).on('click', function(e){
      e.preventDefault();

      cartPriceRuleFormBehaviors.loadForm(null, $(this).data('coupon_edit'), 'GET', 'Edit Coupon');
    });

    $('[data-coupon_delete]', context).on('click', function(e){
      e.preventDefault();
    });
  },
  deleteCoupon: function(){
    App.blockUI({
      target: '#coupon-section',
      boxed: true,
      message: 'Deleting coupon...'
    });

    $.ajax($(this).data('coupon_delete'), {
      method: 'POST',
      success: function(data){
        $.bootstrapGrowl(data.message, {
          ele: 'body', // which element to append to
          type: 'success', // (null, 'info', 'danger', 'success')
          offset: {from: 'top', amount: 20}, // 'top', or 'bottom'
          align: 'right', // ('left', 'right', or 'center')
          width: 250, // (integer, or 'auto')
          delay: 4000, // Time while the message will be displayed. It's not equivalent to the *demo* timeOut!
          allow_dismiss: true, // If true then will display a cross to close the popup.
          stackup_spacing: 10 // spacing between consecutively stacked growls.
        });

        cartPriceRuleFormBehaviors.refreshIndex();
      }
    });
  },
  loadForm: function(formData, formUrl, method, message){
    if(typeof method === 'undefined'){
      method = 'GET';
    }

    if(typeof message === 'undefined'){
      message = 'Loading form...';
    }

    if(typeof formUrl === 'undefined'){
      formUrl = $('#coupon-form-wrapper').data('coupon_form');
    }

    App.blockUI({
      target: '#coupon-section',
      boxed: true,
      message: message
    });

    $.ajax(formUrl, {
      'method': method,
      'data': formData,
      'success': function(data){
        var $form = $(data.html);

        $('#coupon-form-wrapper').html($form);

        formBehaviors.init($form);
        cartPriceRuleFormBehaviors.initAjax($form);
        cartPriceRuleFormBehaviors.handleCustomer($form);
        App.initAjax();
      },
      'error': function(){
        alert('An error occured. Please refresh this page.');
      }
    });
  },
  closeForm: function()
  {
    $('#coupon-form-wrapper').empty();
  },
  refreshIndex: function()
  {
    $.ajax($('#coupon-wrapper').data('coupon_index'), {
      'method': 'GET',
      'success': function(data){
        var $index = $(data.html);

        $('#coupon-wrapper').html($index);

        formBehaviors.init($index);
        App.initAjax();
        cartPriceRuleFormBehaviors.handleEditCoupon('#coupon-wrapper');
      }
    });
  },
  handleCustomer: function(context)
  {
    $('#customer', context).bind('typeahead:select', function(e, suggestion){
      if(suggestion.id){
        $('#customer-id-value').val(suggestion.id);
      }else{
        $('#customer-id-value').val(null);
      }
    });
  }
};