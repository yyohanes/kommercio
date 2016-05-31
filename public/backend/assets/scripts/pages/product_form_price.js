var ProductFormPrice = function () {
    $priceRuleFormUrl = $('#price-rule-form-wrapper').data('price_rule_form');

    var handlePriceRules = function () {
        $('#price-rule-add-btn').click(function(e){
            e.preventDefault();

            ProductFormPrice.loadForm('?new_form');
        });
    }

    return {

        //main function to initiate the module
        init: function () {
            handlePriceRules();

            $(document).ajaxComplete(function( event,request, settings ) {
                App.unblockUI('#tab_price');
            });

            ProductFormPrice.initAjax();
        },
        initAjax: function(context) {
            $('#price-rule-save', context).click(function(e){
                e.preventDefault();

                $('[data-inputmask]', context).inputmask('remove');
                formHelper.clearFormError({
                    'wrapper': '#price-rule-form-wrapper'
                });

                App.blockUI({
                    target: '#tab_price',
                    boxed: true,
                    message: 'Saving price rule...'
                });

                $.ajax($(this).data('price_rule_save'), {
                    'method': 'POST',
                    'data': $('#price-rule-form-wrapper :input').serialize(),
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

                            ProductFormPrice.closeForm();
                            ProductFormPrice.refreshIndex();
                        }
                    },
                    'error': function(xhr){
                        for(var i in xhr.responseJSON){
                            var $errorName = formHelper.convertDotToSquareBracket(i);
                            formHelper.addFieldError({
                                'name': $errorName,
                                'message': xhr.responseJSON[i][0],
                                'context': '#price-rule-form-wrapper'
                            });

                            App.scrollTo($('#price-rule-form-wrapper'));
                        }

                        formBehaviors.initComponents(context);
                    }
                });
            });

            $('#price-rule-cancel', context).click(function(e){
                e.preventDefault();

                ProductFormPrice.closeForm();
            });

            $('[data-price_rule_delete]', context).on('click', function (e) {
                e.preventDefault();
            });

            $('.price-rule-edit-btn', context).on('click', function(e){
                e.preventDefault();

                ProductFormPrice.loadForm('?edit_form', 'Loading edit form...', $(this).data('price_rule_edit'));
            });
        },
        loadForm: function(formData, message, formUrl){
            if(typeof message === 'undefined'){
                message = 'Loading form...';
            }

            if(typeof formUrl === 'undefined'){
                formUrl = $priceRuleFormUrl;
            }

            $('#price-rule-form-wrapper').removeData('price_rule_form');
            $('#price-rule-form-wrapper').attr('data-price_rule_form', formUrl);

            App.blockUI({
                target: '#tab_price',
                boxed: true,
                message: message
            });

            $.ajax(formUrl, {
                'method': 'POST',
                'data': formData,
                'success': function(data){
                    var $priceRuleForm = $(data.html);

                    $('#price-rule-form-wrapper').html($priceRuleForm);
                    App.unblockUI('#tab_price');

                    formBehaviors.init($priceRuleForm);
                    ProductFormPrice.initAjax($priceRuleForm);
                    App.initAjax();
                },
                'error': function(){
                    alert('An error occured. Please refresh this page.');
                }
            });
        },
        closeForm: function(){
            $('#price-rule-form-wrapper').empty();
            $('#price-rule-form-wrapper').attr('data-price_rule_form', $priceRuleFormUrl);
        },
        refreshIndex: function()
        {
            $.ajax($('#price-rule-form-wrapper').data('price_rule_index'), {
                'method': 'GET',
                'success': function(data){
                    var $priceRuleIndex = $(data.html);
                    console.log($priceRuleIndex);

                    $('#price-rules-wrapper').html($priceRuleIndex);

                    formBehaviors.init($priceRuleIndex);
                    ProductFormPrice.initAjax($priceRuleIndex);
                    App.initAjax();
                }
            });
        },
        deletePriceRule: function()
        {
            App.blockUI({
                target: '#tab_price',
                boxed: true,
                message: 'Deleting price rule...'
            });

            $.ajax($(this).data('price_rule_delete'), {
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

                    ProductFormPrice.refreshIndex();
                }
            });
        }
    };
}();

jQuery(document).ready(function() {
    ProductFormPrice.init();
});