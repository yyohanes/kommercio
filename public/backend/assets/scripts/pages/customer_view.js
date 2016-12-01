var CustomerView = function () {
  var handleAddressForm = function()
  {
      $('#address-add-btn').on('click', function(e){
          e.preventDefault();

          addressFormBehaviors.loadForm('?new_form');
      });
  }

  var handleRewardPointForm = function()
  {
    $('.reward-point-action-button').on('click', function(e){
      e.preventDefault();

      rewardPointFormBehaviors.loadForm('?new_form', $(this).data('form'));
    });
  }

  return {
    //main function to initiate the module
    init: function () {
      handleAddressForm();
      handleRewardPointForm();

      addressFormBehaviors.handleEditAddress('#address-index-wrapper');

      $(document).ajaxComplete(function( event,request, settings ) {
          App.unblockUI('#customer-wrapper');
      });
    }
  };
}();

var addressFormBehaviors = {
    initAjax: function(context){
        $('#address-save', context).click(function(e){
            e.preventDefault();

            App.blockUI({
                target: '#customer-wrapper',
                boxed: true,
                message: 'Saving address...'
            });

            formHelper.clearFormError({
                'wrapper': '#address-form-wrapper'
            });

            $.ajax($(this).data('address_save'), {
                'method': 'POST',
                'data': $('#address-form-wrapper :input').serialize(),
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

                        addressFormBehaviors.closeForm();
                        addressFormBehaviors.refreshIndex();
                    }
                },
                'error': function(xhr){
                    for(var i in xhr.responseJSON){
                        var $errorName = formHelper.convertDotToSquareBracket(i);
                        formHelper.addFieldError({
                            'name': $errorName,
                            'message': xhr.responseJSON[i][0],
                            'context': '#address-form-wrapper',
                            'messagesWrapper' : '#address-messages'
                        });
                    }

                    App.scrollTo($('#address-form-wrapper'));

                    formBehaviors.initComponents(context);
                }
            });
        });

        $('#address-cancel', context).on('click', function(e){
            e.preventDefault();

            addressFormBehaviors.closeForm();
        });
    },
    handleEditAddress: function(context){
        $('.address-edit-btn', context).on('click', function(e){
            e.preventDefault();

            addressFormBehaviors.loadForm(null, $(this).data('address_edit'), 'GET', 'Edit Address');
        });

        $('.address-delete-btn', context).on('click', function(e){
            e.preventDefault();
        });
    },
    deleteAddress: function(){
        App.blockUI({
            target: '#customer-wrapper',
            boxed: true,
            message: 'Deleting address...'
        });

        $.ajax($(this).data('address_delete'), {
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

                addressFormBehaviors.refreshIndex();
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
            formUrl = $('#address-form-wrapper').data('address_form');
        }

        App.blockUI({
            target: '#customer-wrapper',
            boxed: true,
            message: message
        });

        $.ajax(formUrl, {
            'method': method,
            'data': formData,
            'success': function(data){
                var $addressForm = $(data.html);

                $('#address-form-wrapper').html($addressForm);

                formBehaviors.init($addressForm);
                addressFormBehaviors.initAjax($addressForm);
                App.initAjax();
            },
            'error': function(){
                alert('An error occured. Please refresh this page.');
            }
        });
    },
    closeForm: function()
    {
        $('#address-form-wrapper').empty();
    },
    refreshIndex: function()
    {
        $.ajax($('#address-form-wrapper').data('address_index'), {
            'method': 'GET',
            'success': function(data){
                var $addressIndex = $(data.html);

                $('#address-index-wrapper').html($addressIndex);

                formBehaviors.init($addressIndex);
                App.initAjax();
                addressFormBehaviors.handleEditAddress('#address-index-wrapper');
            }
        });
    }
};

var rewardPointFormBehaviors = {
  initAjax: function(context){
    $('#reward-point-save', context).on('click', function(e){
      e.preventDefault();

      App.blockUI({
        target: '#customer-wrapper',
        boxed: true,
        message: 'Saving Reward Point...'
      });

      formHelper.clearFormError({
        'wrapper': '#reward-point-form-wrapper'
      });

      $.ajax($(this).data('reward_point_save'), {
        'method': 'POST',
        'data': $('#reward-point-form-wrapper :input').serialize(),
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

            rewardPointFormBehaviors.closeForm();
            rewardPointFormBehaviors.refreshIndex();
          }
        },
        'error': function(xhr){
          for(var i in xhr.responseJSON){
            var $errorName = formHelper.convertDotToSquareBracket(i);
            formHelper.addFieldError({
              'name': $errorName,
              'message': xhr.responseJSON[i][0],
              'context': '#reward-point-form-wrapper',
              'messagesWrapper' : '#reward-point-messages'
            });
          }

          App.scrollTo($('#reward-point-form-wrapper'));

          formBehaviors.initComponents(context);
        }
      });
    });

    $('#reward-point-cancel', context).on('click', function(e){
      e.preventDefault();

      rewardPointFormBehaviors.closeForm();
    });
  },
  loadForm: function(formData, formUrl, method, message){
    if(typeof method === 'undefined'){
      method = 'GET';
    }

    if(typeof message === 'undefined'){
      message = 'Loading form...';
    }

    App.blockUI({
      target: '#customer-wrapper',
      boxed: true,
      message: message
    });

    $.ajax(formUrl, {
      'method': method,
      'data': formData,
      'success': function(data){
        var $form = $(data.html);

        $('#reward-point-form-wrapper').html($form);

        formBehaviors.init($form);
        rewardPointFormBehaviors.initAjax($form);
        App.initAjax();
      },
      'error': function(){
        alert('An error occured. Please refresh this page.');
      }
    });
  },
  closeForm: function()
  {
    $('#reward-point-form-wrapper').empty();
  },
  refreshIndex: function()
  {
    $.ajax($('#reward-point-form-wrapper').data('reward_point_index'), {
      'method': 'GET',
      'success': function(data){
        var $rewardPointIndex = $(data.html);

        $('.current-reward-point').text(data.current_reward_points);

        $('#reward-point-index-wrapper').html($rewardPointIndex);

        formBehaviors.init($addressIndex);
        App.initAjax();
        rewardPointFormBehaviors.handleEditAddress('#reward-point-index-wrapper');
      }
    });
  }
};

jQuery(document).ready(function() {
    CustomerView.init();
});