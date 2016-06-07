var OrderForm = function () {
    var $orderProductTotal;
    var $orderOriginalProductTotal;
    var $orderFeeTotal;
    var $orderTaxTotal;
    var $orderShippingTotal;
    var $taxes = {};
    var $totalShippingLineItems = 0;
    var $orderPriceRuleTotal;
    var $cartPriceRules = {};

    var reserOrderSummary = function()
    {
        $orderProductTotal = 0;
        $orderOriginalProductTotal = 0;
        $orderFeeTotal = 0;
        $orderShippingTotal = 0;
        $orderTaxTotal = 0;
        $orderPriceRuleTotal = 0;

        for(var i in $taxes){
            $taxes[i].total = 0;
        }

        for(var i in $cartPriceRules){
            $cartPriceRules[i].total = 0;
        }
    }

    var calculateTaxes = function($amount)
    {
        for(var i in $taxes){
            var $taxAmount = $amount * ($taxes[i].rate/100);
            $taxes[i].total += $taxAmount;
            $orderTaxTotal += $taxAmount;
        }
    }

    var calculateOrderPriceRules = function()
    {
        for(var i in $cartPriceRules){
            var calculated = 0;

            if($cartPriceRules[i].offer_type == 'order_discount'){
                calculated = calculatePriceRuleValue($orderProductTotal + $orderFeeTotal + $orderShippingTotal, $cartPriceRules[i].price, $cartPriceRules[i].modification, $cartPriceRules[i].modification_type);
                $cartPriceRules[i].total = calculated;
            }else if($cartPriceRules[i].offer_type == 'product_discount') {

            }else{
                $cartPriceRules[i].total = 0;
            }

            $orderPriceRuleTotal += calculated;
        }
    }

    var calculateProductPrice = function($price)
    {
        var calculated = 0;

        for(var i in $cartPriceRules){
            if($cartPriceRules[i].offer_type == 'product_discount'){
                calculated = calculatePriceRuleValue($price, $cartPriceRules[i].price, $cartPriceRules[i].modification, $cartPriceRules[i].modification_type);
                $cartPriceRules[i].total += calculated;
            }
        }

        return calculated;
    }

    var calculateOrderSummary = function()
    {
        reserOrderSummary();

        $('.line-item', '#line-items-table').each(function(idx, obj){
            var lineitemTotalAmount = Number($(obj).find('.lineitem-total-amount').inputmask('unmaskedvalue'));

            if($(obj).data('line_item') == 'product'){
                lineitemTotalAmount += calculateProductPrice(lineitemTotalAmount);
                $orderProductTotal += lineitemTotalAmount;
                $orderOriginalProductTotal += Number($(obj).find('.base-price-field').inputmask('unmaskedvalue') * $(obj).find('.quantity-field').val());
            }else if($(obj).data('line_item') == 'fee'){
                $orderFeeTotal += lineitemTotalAmount;
            }else if($(obj).data('line_item') == 'shipping'){
                $lineitemTotalAmount = Number($(obj).find('.lineitem-total-amount').inputmask('unmaskedvalue'));
                $orderShippingTotal += lineitemTotalAmount;
            }

            if($(obj).data('taxable') == '1'){
                calculateTaxes(lineitemTotalAmount);
            }
        });

        calculateOrderPriceRules();
    }

    var printOrderSummary = function()
    {
        $('.subtotal .amount', '#order-summary').text(formHelper.convertNumber(formHelper.roundNumber($orderOriginalProductTotal + $orderFeeTotal)));

        $('.shipping .amount', '#order-summary').text(formHelper.convertNumber(formHelper.roundNumber($orderShippingTotal)));
        if($orderShippingTotal > 0){
            $('.shipping', '#order-summary').show();
        }else{
            $('.shipping', '#order-summary').hide();
        }

        /*
        $('.discount .amount', '#order-summary').text(formHelper.convertNumber($orderProductTotal - $orderOriginalProductTotal));
        if(($orderProductTotal - $orderOriginalProductTotal) != 0){
            $('.discount', '#order-summary').show();
        }else{
            $('.discount', '#order-summary').hide();
        }
        */

        for(var i in $taxes){
            $('.tax[data-tax_id="'+i+'"] .amount', '#order-summary').text(formHelper.convertNumber(formHelper.roundNumber($taxes[i].total)));
        }

        for(var i in $cartPriceRules){
            $('.cart-price-rule[data-cart_price_rule_id="'+i+'"] .amount', '#order-summary').text(formHelper.convertNumber(formHelper.roundNumber($cartPriceRules[i].total)));
        }

        $('.total .amount', '#order-summary').text(formHelper.convertNumber(formHelper.roundNumber($orderProductTotal + $orderShippingTotal + $orderFeeTotal + $orderTaxTotal + $orderPriceRuleTotal)));
    }

    var calculatePriceRuleValue = function(amount, price, modification, modification_type)
    {
        var calculatedAmount = amount;

        if(price != null){
            calculatedAmount = price;
        }

        if(modification_type == 'percent' && modification != null){
            calculatedAmount += (calculatedAmount * modification/100);
        }else if(modification_type == 'amount' && modification != null){
            calculatedAmount += modification;
        }

        return calculatedAmount - amount;
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

    var handleTaxAndPriceRules = function()
    {
        var $taxTemplate = $('#lineitem-tax-template').html();
        var $taxPrototype = Handlebars.compile($taxTemplate);

        $('.tax', '#tax-summary-wrapper').each(function(idx, obj){
            $taxes[$(obj).data('tax_id')] = {
                rate: $(obj).data('tax_rate'),
                total: 0
            }
        });

        var $cartPriceRuleTemplate = $('#lineitem-cart-price-rule-template').html();
        var $cartPriceRulePrototype = Handlebars.compile($cartPriceRuleTemplate);

        $('#order-form').on('order.major_change', function(e){
            $.ajax(global_vars.get_order_cart_rules_path, {
                method: 'POST',
                data: $('#order-form').serialize(),
                success: function(data){
                    $cartPriceRules = {};
                    $('#cart-price-rules-wrapper').empty();
                    for(var i in data.data){
                        $cartPriceRules[data.data[i].id] = {
                            offer_type: data.data[i].offer_type,
                            price: data.data[i].price,
                            modification: data.data[i].modification,
                            modification_type: data.data[i].modification_type,
                            total: 0
                        }

                        $('#cart-price-rules-wrapper').append($cartPriceRulePrototype({label:data.data[i].name, value:0, 'cart_price_rule_id': data.data[i].id}));
                    }

                    $.ajax(global_vars.get_tax_path, {
                        data: 'country_id='+$('#profile\\[country_id\\]').val()+'state_id='+$('#profile\\[state_id\\]').val()+'city_id='+$('#profile\\[city_id\\]').val()+'district_id='+$('#profile\\[district_id\\]').val()+'area_id='+$('#profile\\[area_id\\]').val(),
                        success: function(data) {
                            $taxes = {};
                            $('#tax-summary-wrapper').empty();
                            for(var i in data.data){
                                $taxes[data.data[i].id] = {
                                    rate: data.data[i].rate,
                                    total: 0
                                }
                                $('#tax-summary-wrapper').append($taxPrototype({label:data.data[i].name+' ('+data.data[i].rate+'%)', value:0, rate:data.data[i].rate, 'tax_id': data.data[i].id}));
                            }

                            calculateOrderSummary();
                            printOrderSummary();
                        }
                    });
                }
            });
        });

        $('#order-form').trigger('order.major_change');
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
                data: $('#order-form').serialize(),
                method: 'POST',
                dataType: 'json',
                success: function(data){
                    if(Object.keys(data).length > 0){
                        var $shippingSelect = $('<select class="form-control"></select>');

                        for(var i in data){
                            $shippingSelect.append('<option data-name="'+data[i].name+'" data-price="'+data[i].price.amount+'" data-shipping_method="'+data[i].shipping_method_id+'" value="'+i+'">'+data[i].name+': '+global_vars.currencies[global_vars.default_currency].iso +' '+ formHelper.convertNumber(data[i].price.amount)+'</option>');
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
            var $newShippingLineItem = $($shippingLineItemPrototype({key: $nextIndex, shipping_method_id:$selectedShippingOption.data('shipping_method')}));

            $('#line-items-table tbody').append($newShippingLineItem);

            $newShippingLineItem.find('.name-field').val($selectedShippingOption.data('name'));
            $newShippingLineItem.find('.base-price-field').val($selectedShippingOption.data('price'));
            $newShippingLineItem.find('.lineitem-total-amount').val($selectedShippingOption.data('price'));

            formBehaviors.init($newShippingLineItem);
            OrderForm.lineItemInit($newShippingLineItem);

            $newShippingLineItem.find('.lineitem-total-amount').trigger('change');

            $('#shipping-options-wrapper').hide()

            $totalShippingLineItems += 1;
            toggleAddShippingButton();
        });

        $('.shipping-cancel', '#shipping-options-wrapper').on('click', function(e){
            e.preventDefault();

            $('#shipping-options-wrapper').hide()

            toggleAddShippingButton();
        });

        $('#order-clear').click(function(e){
            e.preventDefault();

            $('#line-items-table tbody').empty();

            $totalShippingLineItems = 0;
            toggleAddShippingButton();
        });
    }

    var toggleAddShippingButton = function()
    {
        if($totalShippingLineItems > 0){
            $('#add-shipping-lineitem').hide();
        }else{
            $('#add-shipping-lineitem').show();
        }
    }

    return {

        //main function to initiate the module
        init: function () {
            handleBillingEmail();
            handleButtons();
            handleTaxAndPriceRules();

            $totalShippingLineItems = $('.line-item[data-line_item="shipping"]', '#line-items-table').length;
            toggleAddShippingButton();

            $('.line-item', '#line-items-table').each(function(idx, obj){
                OrderForm.lineItemInit($(obj));
            });
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

            $('.line-item-remove', $lineItem).on('click', function(e){
                e.preventDefault();

                $lineItem.remove();

                if($lineItemType == 'shipping'){
                    $totalShippingLineItems -= 1;
                    toggleAddShippingButton();
                }

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