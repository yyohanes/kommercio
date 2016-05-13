var OrderForm = function () {
    var handleBillingEmail = function()
    {
        $('#existing_customer').bind('typeahead:select', function(e, suggestion){
            if(suggestion.profile_id){
                App.blockUI({
                    target: '#billing-information-wrapper',
                    boxed: true,
                    message: 'Loading customer...'
                });

                $.ajax($('#billing-information-wrapper').data('profile_source') + '/' + suggestion.profile_id, {
                    method: 'POST',
                    success: function(data){
                        var $information = $(data.data);

                        formBehaviors.init($information);

                        $('#billing-information-wrapper').html($information);

                        App.unblockUI('#billing-information-wrapper');
                    }
                });
            }
        });

        $('#shipping-copy-btn').click(function(e){
            e.preventDefault();

            App.blockUI({
                target: '#shipping-information-wrapper',
                boxed: true,
                message: 'Loading customer...'
            });

            $.ajax($('#shipping-information-wrapper').data('profile_source'), {
                data: $('#billing-information-wrapper :input').serialize() + '&source=profile',
                method: 'POST',
                success: function(data){
                    var $information = $(data.data);

                    formBehaviors.init($information);

                    $('#shipping-information-wrapper').html($information);

                    App.unblockUI('#shipping-information-wrapper');
                }
            });
        });
    }

    return {

        //main function to initiate the module
        init: function () {
            handleBillingEmail();

            this.lineItemInit('#line-items-table');
        },
        lineItemInit: function(context)
        {
            $('.product-search', context).bind('typeahead:select', function(e, suggestion){
                App.blockUI({
                    target: $(e.target).closest('.line-item'),
                    boxed: true,
                    message: 'Loading product...'
                });

                $.ajax(global_vars.product_line_item + '/' + suggestion.id, {
                    method: 'POST',
                    success: function(data){
                        App.unblockUI($(e.target).closest('.line-item'));

                        var $row = $(data.data);

                        formBehaviors.init($row);
                        OrderForm.lineItemInit($row);

                        $(e.target).closest('.line-item').replaceWith($row);
                    }
                });
            });
        }

    };
}();

jQuery(document).ready(function() {
    OrderForm.init();
});