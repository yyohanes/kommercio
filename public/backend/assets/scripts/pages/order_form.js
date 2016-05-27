var OrderForm = function () {
    var $orderProductTotal;
    var $orderOriginalProductTotal;
    var $orderFeeTotal;
    var $orderShippingTotal;

    var reserOrderSummary = function()
    {
        $orderProductTotal = 0;
        $orderOriginalProductTotal = 0;
        $orderFeeTotal = 0;
        $orderShippingTotal = 0;
    }

    var calculateOrderSummary = function()
    {
        reserOrderSummary();

        $('.line-item', '#line-items-table').each(function(idx, obj){
            if($(obj).data('line_item') == 'product'){
                $orderProductTotal += Number($(obj).find('.lineitem-total-amount').inputmask('unmaskedvalue'));
                $orderOriginalProductTotal += Number($(obj).find('.base-price-field').inputmask('unmaskedvalue') * $(obj).find('.quantity-field').val());
            }else if($(obj).data('line_item') == 'fee'){
                $orderFeeTotal += Number($(obj).find('.lineitem-total-amount').inputmask('unmaskedvalue'));
            }else if($(obj).data('line_item') == 'shipping'){
                $orderShippingTotal += Number($(obj).find('.lineitem-total-amount').inputmask('unmaskedvalue'));
            }
        });
    }

    var printOrderSummary = function()
    {
        $('.subtotal .amount', '#order-summary').text(formHelper.convertNumber($orderOriginalProductTotal + $orderFeeTotal));
        $('.shipping .amount', '#order-summary').text(formHelper.convertNumber($orderShippingTotal));
        $('.discount .amount', '#order-summary').text(formHelper.convertNumber($orderProductTotal - $orderOriginalProductTotal));
        $('.total .amount', '#order-summary').text(formHelper.convertNumber($orderProductTotal + $orderShippingTotal + $orderFeeTotal));
    }

    var getNextIndex = function()
    {
        $lastLineItem = $('.line-item:last-child', '#line-items-table tbody');

        if($lastLineItem.length > 0){
            $nextIndex = $lastLineItem.data('line_item_key') + 1;
        }else{
            $nextIndex = 0;
        }

        return $nextIndex;
    }

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

                        $('#billing-information-wrapper').html($information);

                        formBehaviors.init($information);

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

                    $('#shipping-information-wrapper').html($information);

                    formBehaviors.init($information);

                    App.unblockUI('#shipping-information-wrapper');
                }
            });
        });
    }

    var handleButtons = function(){
        var $productLineItemPrototypeSource = $('#lineitem-product-template').html();
        var $productLineItemPrototype = Handlebars.compile($productLineItemPrototypeSource);

        var $feeLineItemPrototypeSource = $('#lineitem-fee-template').html();
        var $feeLineItemPrototype = Handlebars.compile($feeLineItemPrototypeSource);

        var $shippingLineItemPrototypeSource = $('#lineitem-shipping-template').html();
        var $shippingLineItemPrototype = Handlebars.compile($shippingLineItemPrototypeSource);

        $('#add-product-lineitem').click(function(e){
            e.preventDefault();
            $nextIndex = getNextIndex();

            var $newProductLineItem = $($productLineItemPrototype({key: $nextIndex}));
            $('#line-items-table tbody').append($newProductLineItem);

            formBehaviors.init($newProductLineItem);
            OrderForm.lineItemInit($newProductLineItem);
        });

        $('#add-fee-lineitem').click(function(e){
            e.preventDefault();
            $nextIndex = getNextIndex();

            var $newFeeLineItem = $($feeLineItemPrototype({key: $nextIndex}));
            $('#line-items-table tbody').append($newFeeLineItem);

            formBehaviors.init($newFeeLineItem);
            OrderForm.lineItemInit($newFeeLineItem);
        });

        $('#add-shipping-lineitem').click(function(e){
            e.preventDefault();
            $nextIndex = getNextIndex();

            App.blockUI({
                target: '#order-content-wrapper',
                boxed: true,
                message: 'Loading shipping options...'
            });

            $.ajax($(this).data('shipping_options'), {
                data: $('#order-form :input').serialize(),
                method: 'POST',
                dataType: 'json',
                success: function(data){
                    if(Object.keys(data).length > 0){
                        var $shippingSelect = $('<select class="form-control"></select>');

                        for(var i in data){
                            $shippingSelect.append('<option data-name="'+data[i].name+'" data-price="'+data[i].price.amount+'" value="'+i+'">'+data[i].name+': '+global_vars.currencies[global_vars.default_currency].iso +' '+ formHelper.convertNumber(data[i].price.amount)+'</option>');
                        }

                        $('#add-shipping-lineitem').hide();
                        $('#shipping-options-wrapper').find('select').remove();
                        $('#shipping-options-wrapper').show().find('.input-group').prepend($shippingSelect);
                    }else{
                        $('#shipping-options-wrapper').hide()
                        $('#add-shipping-lineitem').show();
                    }

                    App.unblockUI('#order-content-wrapper');
                }
            });
        });

        $('.shipping-select', '#shipping-options-wrapper').on('click', function(e){
            e.preventDefault();

            var $selectedShippingOption = $('#shipping-options-wrapper select').find(':selected');
            var $newShippingLineItem = $($shippingLineItemPrototype({key: $nextIndex}));

            $('#line-items-table tbody').append($newShippingLineItem);

            $newShippingLineItem.find('.shipping-method-hidden').val($selectedShippingOption.val());
            $newShippingLineItem.find('.name-field').val($selectedShippingOption.data('name'));
            $newShippingLineItem.find('.base-price-field').val($selectedShippingOption.data('price'));
            $newShippingLineItem.find('.lineitem-total-amount').val($selectedShippingOption.data('price'));

            formBehaviors.init($newShippingLineItem);
            OrderForm.lineItemInit($newShippingLineItem);

            $newShippingLineItem.find('.lineitem-total-amount').trigger('change');

            $('#shipping-options-wrapper').hide()
            $('#add-shipping-lineitem').show();
        });

        $('.shipping-cancel', '#shipping-options-wrapper').on('click', function(e){
            e.preventDefault();

            $('#shipping-options-wrapper').hide()
            $('#add-shipping-lineitem').show();
        });

        $('#order-clear').click(function(e){
            e.preventDefault();

            $('#line-items-table tbody').empty();
        });
    }

    return {

        //main function to initiate the module
        init: function () {
            handleBillingEmail();
            handleButtons();

            $('.line-item', '#line-items-table').each(function(idx, obj){
                OrderForm.lineItemInit($(obj));
            });

            $('.line-item:last-child .lineitem-total-amount', '#line-items-table').trigger('change');
        },
        lineItemInit: function(lineItem)
        {
            var $lineItem = lineItem;
            var $lineItemType = $lineItem.data('line_item');

            $('.product-search', lineItem).bind('typeahead:select', function(e, suggestion){
                App.blockUI({
                    target: $lineItem,
                    boxed: true,
                    message: 'Loading product...'
                });

                $.ajax(global_vars.product_line_item + '/' + suggestion.id, {
                    method: 'POST',
                    data: 'product_index=' + $lineItem.data('line_item_key'),
                    success: function(data){
                        App.unblockUI($lineItem);

                        var $row = $(data.data);
                        $lineItem.replaceWith($row);

                        formBehaviors.init($row);
                        OrderForm.lineItemInit($row);

                        $row.find('.net-price-field').trigger('change');
                    }
                });
            });

            $('.line-item-remove', lineItem).on('click', function(e){
                e.preventDefault();

                $lineItem.remove();

                calculateOrderSummary();
                printOrderSummary();
            });

            $('.quantity-field, .net-price-field', lineItem).each(function(idx, obj){
                var $totalAmount;
                $(obj).on('change', function(e){
                    $totalAmount = $lineItem.find('.quantity-field').val() * $lineItem.find('.net-price-field').inputmask('unmaskedvalue');
                    $lineItem.find('.lineitem-total-amount').val($totalAmount).trigger('change');
                });
            });

            $lineItem.find('.lineitem-total-amount').on('change', function(e){
                calculateOrderSummary();
                printOrderSummary();
            });
        }

    };
}();

jQuery(document).ready(function() {
    OrderForm.init();
});