var OrderForm = function () {
    var $orderProductTotal;
    var $orderOriginalProductTotal;
    var $orderFeeTotal;
    var $orderShippingTotal;
    var $orderSubtotal;
    var $orderTotalRounding;
    var $orderTotal;
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
        $orderPriceRuleTotal = 0;
        $orderSubtotal = 0;
        $orderTotalRounding = 0;
        $orderTotal = 0;

        for(var i in $taxes){
            $taxes[i].total = 0;
        }

        for(var i in $cartPriceRules){
            $cartPriceRules[i].total = 0;
        }
    }

    var totalOrderSummary = function()
    {
        //Round tax & price rules to prevent weird long decimal
        $orderProductTotal = formHelper.roundNumber($orderProductTotal);
        $orderOriginalProductTotal = formHelper.roundNumber($orderOriginalProductTotal);
        $orderFeeTotal = formHelper.roundNumber($orderFeeTotal);
        $orderShippingTotal = formHelper.roundNumber($orderShippingTotal);
        $orderPriceRuleTotal = formHelper.roundNumber($orderPriceRuleTotal);

        $orderSubtotal = $orderOriginalProductTotal + $orderFeeTotal;

        calculateOrderPriceRules();

        //Round tax & price rules to prevent weird long decimal
        for(var i in $cartPriceRules){
            $cartPriceRules[i].total = formHelper.roundNumber($cartPriceRules[i].total);
        }

        for(var i in $taxes){
            $taxes[i].total = formHelper.roundNumber($taxes[i].total);
        }

        $orderTotalBeforeRounding = $orderProductTotal + $orderShippingTotal + $orderFeeTotal + $orderPriceRuleTotal;
        $orderTotal = formHelper.roundNumber($orderTotalBeforeRounding, global_vars.total_precision, 'floor');

        $orderTotalRounding += formHelper.calculateRounding($orderTotalBeforeRounding, $orderTotal, true);
    }

    var calculateTaxes = function($amount, $quantity)
    {
        if(typeof $quantity === 'undefined'){
            $quantity = 1;
        }

        var $thisTaxAmount = 0;

        for(var i in $taxes){
            var $taxAmount = {
                gross: 0,
                net: 0
            };
            $taxAmount.gross = formHelper.roundNumber($amount * ($taxes[i].rate/100));
            $taxAmount.net = formHelper.roundNumber($taxAmount.gross);
            $thisTaxAmount += $taxAmount.net;

            $orderTotalRounding += formHelper.calculateRounding($taxAmount.gross, $taxAmount.net, true) * $quantity;

            $taxes[i].total += $taxAmount.net * $quantity;
        }

        return $thisTaxAmount;
    }

    var calculateOrderPriceRules = function()
    {
        for(var i in $cartPriceRules){
            var calculated = {
                net: 0,
                gross: 0
            };

            if($cartPriceRules[i].offer_type == 'order_discount'){
                calculated.gross = calculatePriceRuleValue($orderProductTotal + $orderFeeTotal, $cartPriceRules[i].price, $cartPriceRules[i].modification, $cartPriceRules[i].modification_type);
                calculated.net = formHelper.roundNumber(calculated.gross);

                $orderTotalRounding += formHelper.calculateRounding(calculated.gross, calculated.net, true);

                $cartPriceRules[i].total = calculated.net;
            }else if($cartPriceRules[i].offer_type == 'product_discount') {

            }else{
                $cartPriceRules[i].total = 0;
            }

            $orderPriceRuleTotal += calculated.net;
        }
    }

    //$quantity argument is required to sum total of each rules.
    //Return calculated price for single product
    var calculateProductCartPrice = function($price, $quantity)
    {
        var calculated = {
            net: 0,
            gross: 0
        };

        for(var i in $cartPriceRules){
            if($cartPriceRules[i].offer_type == 'product_discount'){
                calculated.gross = calculatePriceRuleValue($price, $cartPriceRules[i].price, $cartPriceRules[i].modification, $cartPriceRules[i].modification_type);
                calculated.net = formHelper.roundNumber(calculated.gross);

                $orderTotalRounding += formHelper.calculateRounding(calculated.gross, calculated.net, true) * $quantity;

                $cartPriceRules[i].total += calculated.net * $quantity;
            }
        }

        return calculated.net;
    }

    var calculateOrderSummary = function()
    {
        reserOrderSummary();

        var lineitemTotalAmount, lineitemNetAmount, lineitemQuantity;

        $('.line-item', '#line-items-table').each(function(idx, obj){
            //Total line item after discount & tax
            lineitemNetAmount = Number($(obj).find('.net-price-field').inputmask('unmaskedvalue'));
            lineitemQuantity = Number($(obj).find('.quantity-field').inputmask('unmaskedvalue'));

            if($(obj).data('line_item') == 'product'){
                //Before discount & tax
                $orderOriginalProductTotal += lineitemNetAmount * lineitemQuantity;

                //Single unit price before tax
                lineitemNetAmount += calculateProductCartPrice(lineitemNetAmount, lineitemQuantity);

                if($(obj).data('taxable') == '1'){
                    //Single unit price after tax
                    lineitemNetAmount += calculateTaxes(lineitemNetAmount, lineitemQuantity);
                }

                lineitemTotalAmount = lineitemNetAmount * lineitemQuantity;

                $orderProductTotal += lineitemTotalAmount;
            }else if($(obj).data('line_item') == 'fee'){
                lineitemTotalAmount = Number($(obj).find('.lineitem-total-amount').inputmask('unmaskedvalue'));

                if($(obj).data('taxable') == '1'){
                    //Single unit price after tax
                    lineitemTotalAmount += calculateTaxes(lineitemTotalAmount);
                    lineitemTotalAmount = formHelper.roundNumber(lineitemTotalAmount);
                }

                $orderFeeTotal += lineitemTotalAmount;
            }else if($(obj).data('line_item') == 'shipping'){
                lineitemTotalAmount = Number($(obj).find('.lineitem-total-amount').inputmask('unmaskedvalue'));

                if($(obj).data('taxable') == '1'){
                    //Single unit price after tax
                    lineitemTotalAmount += calculateTaxes(lineitemTotalAmount);
                    lineitemTotalAmount = formHelper.roundNumber(lineitemTotalAmount);
                }

                $orderShippingTotal += lineitemTotalAmount;
            }
        });

        totalOrderSummary();
    }

    var printOrderSummary = function()
    {
        $('.subtotal .amount', '#order-summary').text(formHelper.convertNumber($orderSubtotal));

        if($orderShippingTotal > 0){
            $('.shipping', '#order-summary').show();
        }else{
            $('.shipping', '#order-summary').hide();
        }
        $('.shipping .amount', '#order-summary').text(formHelper.convertNumber($orderShippingTotal));

        /*
        $('.discount .amount', '#order-summary').text(formHelper.convertNumber($orderProductTotal - $orderOriginalProductTotal));
        if(($orderProductTotal - $orderOriginalProductTotal) != 0){
            $('.discount', '#order-summary').show();
        }else{
            $('.discount', '#order-summary').hide();
        }
        */

        for(var i in $taxes){
            $('.tax[data-tax_id="'+i+'"] .amount', '#order-summary').text(formHelper.convertNumber($taxes[i].total));
        }

        for(var i in $cartPriceRules){
            $('.cart-price-rule[data-cart_price_rule_id="'+i+'"] .amount', '#order-summary').text(formHelper.convertNumber($cartPriceRules[i].total));
        }

        if($orderTotalRounding > 0 || $orderTotalRounding < 0){
            $('.rounding', '#order-summary').show();
        }else{
            $('.rounding', '#order-summary').hide();
        }
        $('.rounding .amount', '#order-summary').text(formHelper.convertNumber($orderTotalRounding));

        $('.total .amount', '#order-summary').text(formHelper.convertNumber($orderTotal));
    }

    var calculatePriceRuleValue = function(amount, price, modification, modification_type)
    {
        var calculatedAmount = amount;

        if(price != null){
            calculatedAmount = price;
        }

        if(modification_type == 'percent' && modification != null){
            calculatedAmount += (calculatedAmount * Number(modification)/100);
        }else if(modification_type == 'amount' && modification != null){
            calculatedAmount += Number(modification);
        }

        return formHelper.roundNumber(calculatedAmount - amount);
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
            var priceRuleName, isCoupon;
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

                        isCoupon = !($.trim(data.data[i].coupon_code).length === 0);

                        priceRuleName = isCoupon?'<a class="remove-coupon" data-coupon_id="'+data.data[i].id+'" href="#"><i class="fa fa-remove"></i></a> Coupon (' + data.data[i].coupon_code + ')':data.data[i].name;

                        var $cartPriceRuleRow = $($cartPriceRulePrototype({key: i, label: priceRuleName, is_coupon: (isCoupon?1:0), value:0, 'cart_price_rule_id': data.data[i].id}));
                        handleRemoveCoupon($cartPriceRuleRow);

                        $('#cart-price-rules-wrapper').append($cartPriceRuleRow);
                    }

                    $.ajax(global_vars.get_tax_path, {
                        data: 'country_id='+$('#profile\\[country_id\\]').val()+'&state_id='+$('#profile\\[state_id\\]').val()+'&city_id='+$('#profile\\[city_id\\]').val()+'&district_id='+$('#profile\\[district_id\\]').val()+'&area_id='+$('#profile\\[area_id\\]').val(),
                        success: function(data) {
                            $taxes = {};
                            $('#tax-summary-wrapper').empty();
                            for(var i in data.data){
                                $taxes[data.data[i].id] = {
                                    rate: data.data[i].rate,
                                    total: 0
                                }
                                $('#tax-summary-wrapper').append($taxPrototype({key: i, label:data.data[i].name+' ('+data.data[i].rate+'%)', value:0, rate:data.data[i].rate, 'tax_id': data.data[i].id}));
                            }

                            calculateOrderSummary();
                            printOrderSummary();
                        }
                    });
                }
            });
        });
    }

    var handleBillingEmail = function()
    {
        $('#profile\\[email\\]').bind('typeahead:select', function(e, suggestion){
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

                        $('#order-form').trigger('order.major_change');
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

            $('#order-form').trigger('order.major_change');
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
                            $shippingSelect.append('<option data-name="'+data[i].name+'" data-taxable="'+data[i].taxable+'" data-price="'+data[i].price.amount+'" data-shipping_method="'+data[i].shipping_method_id+'" value="'+i+'">'+data[i].name+': '+global_vars.currencies[global_vars.default_currency].iso +' '+ formHelper.convertNumber(data[i].price.amount)+'</option>');
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
            var $newShippingLineItem = $($shippingLineItemPrototype({key: $nextIndex, taxable: $selectedShippingOption.data('taxable'), shipping_method: $selectedShippingOption.val(), shipping_method_id:$selectedShippingOption.data('shipping_method')}));

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

            $('#order-form').trigger('order.major_change');
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

            $('#order-form').trigger('order.major_change');
        });

        $('#coupon-add-btn').click(function(e){
            e.preventDefault();

            formHelper.clearFormError({
                'wrapper': '#coupons-wrapper',
                'messagesWrapper': '#coupon-messages'
            });

            App.blockUI({
                target: '#coupons-wrapper',
                boxed: true,
                message: 'Adding coupon...'
            });

            $.ajax($(this).data('coupon_add'), {
                'method': 'POST',
                'data': $('#coupons-wrapper :input').serialize(),
                'success': function(data){
                    $('#coupon-field').val('');
                    $('#coupons-wrapper .added-coupon').remove();
                    for(var i in data.data){
                        $('#coupons-wrapper').append('<input type="hidden" name="added_coupons[]" value="'+data.data[i].id+'" class="added-coupon" />');
                    }

                    $('#order-form').trigger('order.major_change');
                },
                'error': function(xhr){
                    for(var i in xhr.responseJSON){
                        var $errorName = formHelper.convertDotToSquareBracket(i);

                        formHelper.addFieldError({
                            'name': $errorName,
                            'message': xhr.responseJSON[i][0],
                            'context': '#coupons-wrapper',
                            'messagesWrapper': '#coupon-messages',
                            'highlightField': false
                        });

                        App.scrollTo($('#coupons-wrapper'));
                    }
                },
                'complete': function(){
                    App.unblockUI('#coupons-wrapper');
                }
            });
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

    var handleRemoveCoupon = function(context){
        $('.remove-coupon', context).on('click', function(e){
            e.preventDefault();

            $('.added-coupon[value="'+$(this).data('coupon_id')+'"]', '#coupons-wrapper').remove();
            $('#order-form').trigger('order.major_change');
        });
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
                $(obj).find('.net-price-field').trigger('change');
            });

            $('#billing-information-wrapper').on('address.change', function(){
                $('#order-form').trigger('order.major_change');
            });

            $('#order-form').trigger('order.major_change');
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

                        $row.find('.quantity-field').focus();

                        $row.find('.net-price-field').trigger('change');

                        $('#order-form').trigger('order.major_change');
                    }
                });
            });

            $('.line-item-remove', $lineItem).on('click', function(e){
                e.preventDefault();

                $lineItem.remove();
                $('#order-form').trigger('order.major_change');

                if($lineItemType == 'shipping'){
                    $totalShippingLineItems -= 1;
                    toggleAddShippingButton();
                }
            });

            $('.quantity-field, .net-price-field', lineItem).each(function(idx, obj){
                var $totalAmount;

                $(obj).on('change', function(e){
                    $totalAmount = $lineItem.find('.quantity-field').val() * $lineItem.find('.net-price-field').inputmask('unmaskedvalue');
                    $totalAmount = formHelper.roundNumber($totalAmount);;
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