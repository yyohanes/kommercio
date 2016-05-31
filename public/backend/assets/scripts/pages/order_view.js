var OrderView = function () {
    var handleOrderPaymentForm = function()
    {
        $('#payment-add-btn').on('click', function(e){
            e.preventDefault();

            orderPaymentFormBehaviors.loadForm('?new_form');
        });
    }

    return {

        //main function to initiate the module
        init: function () {
            handleOrderPaymentForm();

            $(document).ajaxComplete(function( event,request, settings ) {
                App.unblockUI('#tab_payments');
            });
        }
    };
}();

var orderPaymentFormBehaviors = {
    initAjax: function(context){
        $('#payment-save', context).click(function(e){
            e.preventDefault();

            $('[data-inputmask]', context).inputmask('remove');
            formHelper.clearFormError({
                'wrapper': '#payment-form-wrapper'
            });

            App.blockUI({
                target: '#tab_payments',
                boxed: true,
                message: 'Saving payment...'
            });

            $.ajax($(this).data('payment_save'), {
                'method': 'POST',
                'data': $('#payment-form-wrapper :input').serialize(),
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

                        orderPaymentFormBehaviors.closeForm();
                        orderPaymentFormBehaviors.refreshOrderPaymentIndex();
                    }
                },
                'error': function(xhr){
                    for(var i in xhr.responseJSON){
                        var $errorName = formHelper.convertDotToSquareBracket(i);
                        formHelper.addFieldError({
                            'name': $errorName,
                            'message': xhr.responseJSON[i][0],
                            'context': '#payment-form-wrapper'
                        });

                        App.scrollTo($('#payment-form-wrapper'));
                    }

                    formBehaviors.initComponents(context);
                }
            });
        });

        $('#payment-cancel', context).on('click', function(e){
            e.preventDefault();

            orderPaymentFormBehaviors.closeForm();
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
            formUrl = $('#payment-form-wrapper').data('payment_form');
        }

        App.blockUI({
            target: '#tab_payments',
            boxed: true,
            message: message
        });

        $.ajax(formUrl, {
            'method': method,
            'data': formData,
            'success': function(data){
                var $orderPaymentForm = $(data.html);

                $('#payment-form-wrapper').html($orderPaymentForm);

                formBehaviors.init($orderPaymentForm);
                orderPaymentFormBehaviors.initAjax($orderPaymentForm);
                App.initAjax();
            },
            'error': function(){
                alert('An error occured. Please refresh this page.');
            }
        });
    },
    closeForm: function()
    {
        $('#payment-form-wrapper').empty();
    },
    refreshOrderPaymentIndex: function()
    {
        $.ajax($('#payment-form-wrapper').data('payment_index'), {
            'method': 'GET',
            'success': function(data){
                var $orderPaymentIndex = $(data.html);

                $('#payment-index-wrapper').html($orderPaymentIndex);

                formBehaviors.init($orderPaymentIndex);
                App.initAjax();
            }
        });
    }
};

jQuery(document).ready(function() {
    OrderView.init();
});