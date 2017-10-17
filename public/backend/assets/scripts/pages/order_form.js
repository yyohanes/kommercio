var OrderForm = function () {
  var $orderProductSubtotal;
  var $orderTaxTotal;
  var $orderTaxErrorTotal;
  var $orderFeeTotal;
  var $orderShippingTotal;
  var $orderSubtotal;
  var $orderTotalBeforeRounding;
  var $orderTotalRounding;
  var $orderTotalBeforeTax;
  var $orderTotal;
  var $orderRewardPoints;
  var $taxes = {};
  var $rewardRules = [];
  var $totalShippingLineItems = 0;
  var $orderPriceRuleTotal;
  var $cartPriceRules = {};
  var $orderedTotal = {};
  var $disabledDates = [];
  var $lineItems = {};
  var $categoryOrderLimits = {};

  var $taxTemplate = $('#lineitem-tax-template').html();
  var $taxPrototype = Handlebars.compile($taxTemplate);

  var $cartPriceRuleTemplate = $('#lineitem-cart-price-rule-template').html();
  var $cartPriceRulePrototype = Handlebars.compile($cartPriceRuleTemplate);

  var $rewardRuleTemplate = $('#reward-rule-template').html();
  var $rewardRulePrototype = Handlebars.compile($rewardRuleTemplate);

  var resetRewardPoints = function()
  {
    $orderRewardPoints = 0;

    for(var i in $rewardRules){
      $rewardRules[i].total = 0;
    }
  }

  var reserOrderSummary = function()
  {
    $orderProductSubtotal = 0;
    $orderTaxTotal = 0;
    $orderTaxErrorTotal = 0;
    $orderFeeTotal = 0;
    $orderShippingTotal = 0;
    $orderPriceRuleTotal = 0;
    $orderSubtotal = 0;
    $orderTotalBeforeRounding = 0;
    $orderTotalRounding = 0;
    $orderTotalBeforeTax = 0;
    $orderTotal = 0;

    for(var i in $cartPriceRules){
      $cartPriceRules[i].total = 0;
    }

    for(var i in $taxes){
      $taxes[i].total = 0;
      $taxes[i].error = 0;
    }
  }

  var getTotalOrderBeforeExtras = function()
  {
    return $orderProductSubtotal + $orderFeeTotal + $orderPriceRuleTotal;
  }

  var totalOrderSummary = function()
  {
    $orderSubtotal = $orderProductSubtotal + $orderFeeTotal;
    $orderSubtotal = formHelper.roundNumber($orderSubtotal);

    for(var i in $lineItems){
      //Calculate Cart Price Rules
      for(var j in $lineItems[i].cartPriceRules){
        if(typeof $cartPriceRules[j] === 'undefined'){
          delete $lineItems[i].cartPriceRules[j];
        }else{
          $cartPriceRules[j].total += formHelper.roundNumber($lineItems[i].cartPriceRules[j]);
          $cartPriceRules[j].total = formHelper.roundNumber($cartPriceRules[j].total);
          $orderPriceRuleTotal += $lineItems[i].cartPriceRules[j];
        }
      }

      //Calculate Taxes
      for(var j in $lineItems[i].taxes){
        $orderTaxTotal += $lineItems[i].taxes[j];
        $taxes[j].total += $lineItems[i].taxes[j];
        $taxes[j].total = formHelper.roundNumber($taxes[j].total);
      }
    }

    $orderTotalBeforeRounding = getTotalOrderBeforeExtras() + $orderShippingTotal + $orderTaxTotal;
    $orderTotalBeforeRounding = formHelper.roundNumber($orderTotalBeforeRounding);
    $orderTotal = formHelper.roundNumber($orderTotalBeforeRounding, global_vars.total_precision, 'floor');

    $orderTotalRounding += formHelper.calculateRounding($orderTotalBeforeRounding, $orderTotal);

    //Calculate Tax Error
    for(var i in $taxes){
      $taxes[i].error = formHelper.roundNumber(($orderTotalBeforeTax + $orderPriceRuleTotal) * $taxes[i].rate/100);
      $orderTaxErrorTotal += $taxes[i].error;
    }

    $orderTaxErrorTotal = formHelper.roundNumber($orderTaxTotal - $orderTaxErrorTotal);
  }

  var calculateTax = function($lineItem, $quantity)
  {
    var $thisTaxAmount = 0;

    for(var i in $taxes){
      var $taxAmount = {
        gross: 0,
        net: 0
      };

      $taxAmount.gross = formHelper.roundNumber($lineItem.net * ($taxes[i].rate/100));
      $taxAmount.net = $taxAmount.gross;

      $thisTaxAmount += $taxAmount.net;

      $lineItem.taxes[i] = $taxAmount.net * $quantity;
    }

    $lineItem.net = $lineItem.net + $thisTaxAmount;
    $lineItem.total = $lineItem.net * $quantity;

    return $thisTaxAmount;
  }

  //$quantity argument is required to sum total of each rules.
  //Return calculated price for single product
  var calculateCartPrice = function($lineItem, $quantity)
  {
    var calculated = {
      base: $lineItem.net,
      net: 0,
      gross: 0,
      total: $lineItem.net
    };

    $product_id = ($lineItem.object.data('line_item') == 'product')?$lineItem.object.find('.line-item-id').val():false;

    for(var i in $cartPriceRules){
      if($cartPriceRules[i].offer_type == 'product_discount'){
        if($lineItem.object.data('line_item') == 'product'){
          if($cartPriceRules[i].products.length > 0){
            if($cartPriceRules[i].products.indexOf(Number($product_id)) < 0){
              continue;
            }
          }
        } else {
          continue;
        }
      }else if($cartPriceRules[i].modification_type == 'percent'){

      }else if($cartPriceRules[i].modification_type == 'amount' && $cartPriceRules[i].applied_line_items.length < 1){

      }else{
        continue;
      }

      if($cartPriceRules[i].modification_source == 0){
        calculated.gross = calculatePriceRuleValue(calculated.base, $cartPriceRules[i].price, $cartPriceRules[i].modification, $cartPriceRules[i].modification_type);
      }else{
        calculated.gross = calculatePriceRuleValue(calculated.total, $cartPriceRules[i].price, $cartPriceRules[i].modification, $cartPriceRules[i].modification_type);
      }

      calculated.net = calculated.gross;
      calculated.total += calculated.net;

      if($cartPriceRules[i].applied_line_items.indexOf($lineItem) < 0){
        $cartPriceRules[i].applied_line_items.push($lineItem);
      }

      $lineItem.cartPriceRules[i] = (formHelper.roundNumber(calculated.base + calculated.net) - calculated.base) * $quantity;

      $lineItem.net += calculated.net;
    }

    $lineItem.net = formHelper.roundNumber($lineItem.net);
    $lineItem.total = $lineItem.net * $quantity;

    return calculated.net;
  }

  var calculateOrderSummary = function()
  {
    reserOrderSummary();

    var lineitemTotalAmount, lineitemNetAmount, lineitemQuantity, lineitem;

    $('.line-item', '#line-items-table').each(function(idx, obj){
      //Total line item after discount & tax
      lineitemNetAmount = Number($(obj).find('.net-price-field').inputmask('unmaskedvalue'));
      lineitemQuantity = Number($(obj).find('.quantity-field').inputmask('unmaskedvalue'));
      lineitem = $lineItems[$(obj).data('uid')];

      if($(obj).data('line_item') == 'product'){
        lineitemTotalAmount = lineitemNetAmount * lineitemQuantity;
        $orderProductSubtotal += lineitemTotalAmount;
      }else if($(obj).data('line_item') == 'fee'){
        lineitemTotalAmount = Number($(obj).find('.lineitem-total-amount').inputmask('unmaskedvalue'));

        $orderFeeTotal += lineitemTotalAmount;
      }else if($(obj).data('line_item') == 'shipping'){
        lineitemTotalAmount = Number($(obj).find('.lineitem-total-amount').inputmask('unmaskedvalue'));

        $orderShippingTotal += lineitemTotalAmount;
      }

      if($(obj).data('taxable') == '1'){
        $orderTotalBeforeTax += lineitemTotalAmount;
      }
    });

    totalOrderSummary();
  }

  var calculateLineItemNetPrice = function($lineitem, $price, $quantity)
  {
      if(typeof $quantity === 'undefined'){
          $quantity = 1;
      }

      $lineitem.net = $price;

      if($lineitem.object.data('taxable') == '1'){
          calculateCartPrice($lineitem, $quantity);
          calculateTax($lineitem, $quantity);
      }else{
          calculateCartPrice($lineitem, $quantity);
      }
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

      for(var i in $taxes){
          $('.tax[data-tax_id="'+ i.replace('tax_', '') +'"] .amount', '#order-summary').text(formHelper.convertNumber(formHelper.roundNumber($taxes[i].error)));
      }

      for(var i in $cartPriceRules){
          $('.cart-price-rule[data-cart_price_rule_id="'+ i.replace('cart_price_rule_', '') +'"] .amount', '#order-summary').text(formHelper.convertNumber($cartPriceRules[i].total));
      }

      if($orderTotalRounding > 0 || $orderTotalRounding < 0){
          $('.rounding', '#order-summary').show();
      }else{
          $('.rounding', '#order-summary').hide();
      }
      $('.rounding .amount', '#order-summary').text(formHelper.convertNumber($orderTotalRounding));

      $('.total .amount', '#order-summary').text(formHelper.convertNumber($orderTotal));

      if($orderTaxErrorTotal > 0 || $orderTaxErrorTotal < 0){
          $('.tax-error', '#order-summary').show();
      }else{
          $('.tax-error', '#order-summary').hide();
      }
      $('.tax-error .amount', '#order-summary').text(formHelper.convertNumber($orderTaxErrorTotal));
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

      return calculatedAmount - amount;
  }

  var getNextIndex = function($context)
  {
      if(typeof $context == 'undefined'){
          $context = $('.line-item', '#line-items-table tbody');
      }

      $lastLineItem = $context.last();

      if($lastLineItem.length > 0){
          $nextIndex = $lastLineItem.data('line_item_key') + 1;
      }else{
          $nextIndex = 0;
      }

      return $nextIndex;
  }

  var handleTaxPriceRuleRewardRules = function()
  {
    $('.tax', '#tax-summary-wrapper').each(function(idx, obj){
      $taxes['tax_' + $(obj).data('tax_id')] = {
        rate: $(obj).data('tax_rate'),
        total: 0,
        error: 0
      }
    });

    $('#order-form').on('order.major_change', function(e){
      var priceRuleName, isCoupon;

      $.ajax(global_vars.get_order_cart_rules_path, {
        method: 'POST',
        data: $('#order-form').serialize(),
        success: function(data){
          $cartPriceRules = [];
          $productPriceRulesTotal = {};
          $('#cart-price-rules-wrapper').empty();

          for(var i in data.data){
            $cartPriceRules['cart_price_rule_' + data.data[i].id] = {
              offer_type: data.data[i].offer_type,
              price: data.data[i].price,
              modification: data.data[i].modification,
              modification_type: data.data[i].modification_type,
              modification_source: data.data[i].modification_source,
              total: 0,
              products: data.data[i].products,
              applied_line_items: []
            }

            isCoupon = typeof data.data[i].coupon != 'undefined';

            priceRuleName = isCoupon?'<a class="remove-coupon" data-coupon_id="'+data.data[i].coupon.id+'" href="#"><i class="fa fa-remove"></i></a> Coupon (' + data.data[i].coupon.coupon_code + ')':data.data[i].name;

            var $cartPriceRuleRow = $($cartPriceRulePrototype({key: i, label: priceRuleName, is_coupon: (isCoupon?1:0), value:0, 'cart_price_rule_id': data.data[i].id}));
            handleRemoveCoupon($cartPriceRuleRow);

            $('#cart-price-rules-wrapper').append($cartPriceRuleRow);
          }

          $.ajax(global_vars.get_tax_path, {
            data: 'country_id='+$('#shipping_profile\\[country_id\\]').val()+'&state_id='+$('#shipping_profile\\[state_id\\]').val()+'&city_id='+$('#shipping_profile\\[city_id\\]').val()+'&district_id='+$('#shipping_profile\\[district_id\\]').val()+'&area_id='+$('#shipping_profile\\[area_id\\]').val(),
            success: function(data) {
              $taxes = [];
              $productTaxes = {};
              $('#tax-summary-wrapper').empty();
              for(var i in data.data){
                $taxes['tax_' + data.data[i].id] = {
                  rate: data.data[i].rate,
                  total: 0,
                  error: 0
                }
                $('#tax-summary-wrapper').append($taxPrototype({key: i, label:data.data[i].name+' ('+data.data[i].rate+'%)', value:0, rate:data.data[i].rate, 'tax_id': data.data[i].id}));
              }

              $('.net-price-field', '#order-form').trigger('change');

              calculateOrderSummary();
              printOrderSummary();
            }
          });

          if($('#reward-points-wrapper').length > 0){
            $.ajax(global_vars.get_reward_points_path, {
              data: 'currency='+$('#currency-input').val()+'&store_id='+$('#store-option').val(),
              success: function(data) {
                $rewardRules = [];
                $('.portlet-body', '#reward-points-wrapper').empty();

                for(var i in data.data){
                  $rewardRules['reward_rule_' + data.data[i].id] = {
                    id: data.data[i].id,
                    name: data.data[i].name,
                    type: data.data[i].type,
                    member: data.data[i].member==1?true:false,
                    rule: data.data[i].rule,
                    reward: data.data[i].reward,
                    total : 0
                  }
                }

                calculateRewardRules();
                printRewardRules();
              }
            });
          }
        }
      });
    });
  }

  var handleCategoryOrderLimit = function($lineitem)
  {
    var currentQuantity = Number($lineitem.find('.quantity-field').val());
    var beforeValue = $lineitem.find('.quantity-field').data('beforeValue')?Number($lineitem.find('.quantity-field').data('beforeValue')):0;

    for(var i in $categoryOrderLimits){
      if(isProductCategory($lineitem, $categoryOrderLimits[i].productCategories)){
        $categoryOrderLimits[i].total -= beforeValue;

        $categoryOrderLimits[i].total += currentQuantity;

        if($categoryOrderLimits[i].lineItems.indexOf($lineitem.data('uid')) < 0){
          $categoryOrderLimits[i].lineItems.push($lineitem.data('uid'));
        }
      }
    }

    var relevantCategoryOrderLimits = 0;

    for(var i in $categoryOrderLimits){
      $categoryOrderLimits[i].rendered.find('.limit-total').text(formHelper.roundNumber($categoryOrderLimits[i].limit));
      labelStatus($categoryOrderLimits[i].rendered.find('.ordered-total'), formHelper.roundNumber($categoryOrderLimits[i].total));

      if($categoryOrderLimits[i].lineItems.length > 0){
        $categoryOrderLimits[i].rendered.show();
        relevantCategoryOrderLimits += 1;
      }else{
        $categoryOrderLimits[i].rendered.hide();
      }
    }

    if(relevantCategoryOrderLimits > 0){
      $('#category-limit-wrapper').show();
    }else{
      $('#category-limit-wrapper').hide();
    }
  }

  var handleBillingEmail = function(context)
  {
    if(typeof context === 'undefined'){
      context = document;
    }

    //If one country
    $('.country-select', context).each(function(idx, obj) {
      if ($(obj).find('option').length <= 2 && $(obj).find(':selected').index() == 0) {
        $(obj).val($(obj).find('option:eq(1)').val());
        $(obj).select2();

        $(obj).change();
      }
    });

    $('#profile\\[email\\]', context).bind('typeahead:select', function(e, suggestion){
      if(suggestion.user_id){
        $('#user-id-value').val(suggestion.user_id);
      }else{
        $('#user-id-value').val(null);
      }

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
            handleBillingEmail($information);

            App.unblockUI('#billing-information-wrapper');

            $('#order-form').trigger('order.major_change');
          }
        });
      }
    });

    $('#shipping-copy-btn', context).click(function(e){
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

  var isProductCategory = function($lineItem, $categories){
    if($lineItem.data('product_categories')){
      var $lineItemCategories = String($lineItem.data('product_categories')).split('|');

      //Find intersects
      var ai=0, bi=0;
      var result = [];

      while( ai < $lineItemCategories.length && bi < $categories.length )
      {
        if      ($lineItemCategories[ai] < $categories[bi] ){ ai++; }
        else if ($lineItemCategories[ai] > $categories[bi] ){ bi++; }
        else /* they're equal */
        {
          result.push($lineItemCategories[ai]);
          ai++;
          bi++;
        }
      }

      return result.length > 0;
    }

    return false;
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
        },
        complete: function(){
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
      $newShippingLineItem.find('.net-price-field').val($selectedShippingOption.data('price'));

      formBehaviors.init($newShippingLineItem);
      OrderForm.lineItemInit($newShippingLineItem);

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
        'data': $('#order-form :input').serialize(),
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

    $('.place-order-btn, button[name="action"]', '#order-form').unbind('click');
    $('.place-order-btn, button[name="action"]', '#order-form').click(function(e){
      var problems = checkOverLimit();

      if(problems.length > 0){
        var $confirmed = confirm(problems + "\nAre you sure you want to proceed?");

        if(!$confirmed){
          e.stopImmediatePropagation();
          return $confirmed;
        }
      }

      return true;
    });

    formBehaviors.handleModalAjaxBtn($('#order-form'));
  }

  var handleChildButtons = function(context)
  {
    $('.configured-product-add', context).each(function(idx, obj){
      var $childLineItemHeader = $(this).parents('.child-line-item-header');
      var $key = $childLineItemHeader.data('parent_line_item_key');
      var $composite = $childLineItemHeader.data('composite_id');
      var $maximum = $childLineItemHeader.data('maximum');
      var $childProductLineItemPrototypeSource = $('#lineitem-product-'+$key+'-child-'+$composite+'-template').html();
      var $childProductLineItemPrototype = Handlebars.compile($childProductLineItemPrototypeSource);
      var $childProductLineItems = $('[data-parent_line_item_key="'+$key+'"][data-composite="'+$composite+'"]');

      var checkChildLineItems = function(){
        setTimeout(function(){
          $childProductLineItems = $('[data-parent_line_item_key="'+$key+'"][data-composite="'+$composite+'"]');

          if($childProductLineItems.length < $maximum){
            $(obj).show();
          }else{
            $(obj).hide();
          }
        }, 100);
      }

      if($childProductLineItems.length >= $maximum){
        $(obj).hide();
      }

      $childProductLineItems.each(function(idx, obj){
        $(obj).on('order.line_item_remove', function(){
          checkChildLineItems();
        });
      });

      $(obj).click(function(e){
        e.preventDefault();

        $childProductLineItems = $('[data-parent_line_item_key="'+$key+'"][data-composite="'+$composite+'"]');
        $nextIndex = getNextIndex($childProductLineItems);

        var $newChildProductLineItem = $($childProductLineItemPrototype({childKey: $nextIndex}));

        $newChildProductLineItem.on('order.line_item_remove', function(){
          checkChildLineItems();
        });

        if($childProductLineItems.length > 0){
          $childProductLineItems.last().after($newChildProductLineItem);
        }else{
          $childLineItemHeader.after($newChildProductLineItem);
        }

        if($childProductLineItems.length >= $maximum){
          $(obj).hide();
        }

        formBehaviors.init($newChildProductLineItem);
        OrderForm.lineItemInit($newChildProductLineItem);

        checkChildLineItems();
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

  var handleAvailability = function()
  {
    var datePickerClosed = true;
    var datepickerDate;

    $('#order-form').on('order.delivery_date_change', function(){
      $.ajax(global_vars.get_category_availability, {
        data: $('#order-form').serialize(),
        method: 'POST',
        success: function(data){
          $categoryOrderLimits = [];

          $('tbody', '#category-limit-wrapper').html('');

          for(var i in data){
            data[i].total = 0;
            data[i].rendered = $('<tr><td>'+data[i].label+' <span class="label label-sm label-info"><span class="ordered-total">0</span>/<span class="limit-total">0</span></span></td></tr>');
            data[i].lineItems = [];
            data[i].addedProducts = {};

            $categoryOrderLimits.push(data[i]);

            $('tbody', '#category-limit-wrapper').append(data[i].rendered);
          }

          $('.line-item[data-line_item="product"]', '#line-items-table').each(function(idx, obj){
            if($(obj).find('.line-item-id').val() != ''){
              //Get availability
              $.ajax(global_vars.get_product_availability + '/' + $(obj).find('.line-item-id').val(), {
                method: 'POST',
                data: $('#order-form').serialize(),
                success: function(data){
                  handleLoadedAvailability(data.data, $(obj));
                  handleCategoryOrderLimit($(obj));
                }
              });
            }
          });
        }
      });
    });

    //Availability from Calendar
    $datePicker = $('#delivery_date', '#order-form');
    var today = new Date();
    var disablePastMonths = $datePicker.hasClass('disable-past-months');

    $datePicker.datepicker({
      rtl: App.isRTL(),
      format: 'yyyy-mm-dd',
      beforeShowDay: function(e){
        if($disabledDates.indexOf(e.getFullYear() + '-' + (e.getMonth()+1) + '-' + e.getDate()) > -1){
          return 'disabled-date';
        }

        // Disable past months
        if (disablePastMonths && e.getMonth() < today.getMonth()) {
          return {
            classes: 'disabled-date',
            enabled: false
          };
        }
      }
    }).on('show', function(e){
      if(datePickerClosed){
        datepickerDate = $(e.target).datepicker('getDate');

        if(datepickerDate == null){
          datepickerDate = new Date();
        }

        handleOnChangeMonth(datepickerDate.getMonth(), datepickerDate.getFullYear(), $(e.target).datepicker('getDate'));
      }

      datePickerClosed = false;
    }).on('hide', function(e){
      datePickerClosed = true;
    }).on('changeMonth', function(e){
      handleOnChangeMonth(e.date.getMonth(), e.date.getFullYear());
    }).on('changeDate', function(e){
      if($disabledDates.indexOf(e.date.getFullYear() + '-' + (e.date.getMonth()+1) + '-' + e.date.getDate()) > -1){
        var $confirmed = confirm('This date is not supposed to be selected. Are you sure you want to continue?');

        if(!$confirmed){
          e.preventDefault();
          $datePicker.datepicker('update', '');
        }else{
          $datePicker.datepicker('hide');
        }
      }else{
        $datePicker.datepicker('hide');
      }
    });

    $datePicker.on('changeDate', function(e){
      $('#order-form').trigger('order.delivery_date_change');
    });
    //End Availability from Calendar

    //On store selector change
    $('#store-option').on('change', function(){
      $('#order-form').trigger('order.delivery_date_change');
    });
  }

  var handleOnChangeMonth = function(month, year, date)
  {
      $('#delivery-date-panel .datepicker-days').css('visibility', 'hidden');
      $('#delivery-date-panel .datepicker').addClass('loading');

      $.ajax(global_vars.get_availability_calendar, {
          method: 'POST',
          data: $('#order-form').serialize() + '&month=' + (month+1) + '&year=' + year + '&order_id=' + $('#order-form').data('order_id') + '&internal=1',
          success: function(data){
              $disabledDates = $.makeArray(data.disabled_dates);

              if(typeof date !== 'undefined'){
                  $('#delivery_date', '#order-form').datepicker('update', date);
              }else{
                  $('#delivery_date', '#order-form').datepicker('fill');
              }

              $('#delivery-date-panel .datepicker-days').css('visibility', 'visible');
              $('#delivery-date-panel .datepicker').removeClass('loading');
          },
          complete: function(){

          }
      });
  }

  var handleLoadedAvailability = function(data, row)
  {
    var currentQuantity = Number(row.find('.quantity-field').val());

    $orderedTotal[row.find('.line-item-id').val()] = {
      'ordered_total': data.ordered_total,
      'order_limit': data.order_limit,
      'stock': data.stock
    };

    for(var i in $categoryOrderLimits){
      if($categoryOrderLimits[i].type != 'per_order'){
        if(isProductCategory(row, $categoryOrderLimits[i].productCategories)){
          if(typeof $categoryOrderLimits[i].addedProducts['product_' + row.find('.line-item-id').val()] != 'undefined'){
            $categoryOrderLimits[i].total -= $categoryOrderLimits[i].addedProducts['product_' + row.find('.line-item-id').val()].ordered_total;
          }else{
            $categoryOrderLimits[i].total += data.ordered_total;
          }

          $categoryOrderLimits[i].addedProducts['product_' + row.find('.line-item-id').val()] = {
            ordered_total: data.ordered_total
          }
        }
      }
    }

    if(data.order_limit !== null && data.order_limit !== undefined){
      row.find('.order-limit-info .limit-total').text(formHelper.roundNumber(data.order_limit));
      row.find('.order-limit-info .ordered-total').text(formHelper.roundNumber(data.ordered_total + currentQuantity));
      row.find('.order-limit-info').show();
    }else{
      row.find('.order-limit-info').hide();
    }

    if(data.stock !== null && data.stock !== undefined){
      row.find('.stock-total').text(formHelper.roundNumber(data.stock + savedQuantity - currentQuantity));
      row.find('.stock-info').show();
    }else{
      row.find('.stock-info').hide();
    }
  }

  var checkOverLimit = function()
  {
    var problems = '';

    $('.line-item[data-line_item="product"]', '#line-items-table').each(function(idx, obj){
      if(typeof $orderedTotal[$(obj).find('.line-item-id').val()] != 'undefined'){
        var totalOrdered = Number($(obj).find('.quantity-field').val()) + $orderedTotal[$(obj).find('.line-item-id').val()].ordered_total;

        if((totalOrdered > $orderedTotal[$(obj).find('.line-item-id').val()].order_limit && $orderedTotal[$(obj).find('.line-item-id').val()].order_limit !== null) || (totalOrdered > $orderedTotal[$(obj).find('.line-item-id').val()].stock && $orderedTotal[$(obj).find('.line-item-id').val()].stock !== null)){
          problems += $(obj).find('.product-search').val() + " order quantity exceeds limit.\n";
        }
      }
    });

    for(var i in $categoryOrderLimits){
      if($categoryOrderLimits[i].total > $categoryOrderLimits[i].limit){
        problems += $categoryOrderLimits[i].label + " order quantity exceeds limit.\n";
      }
    }

    return problems;
  }

  var lastRunningIndex = 0;

  var generateUUID = function() {
      lastRunningIndex += 1;
      var $timestamp = new Date().getTime();
      $timestamp = $timestamp + String(lastRunningIndex);

      return $timestamp;
  }

  var calculateRewardRules = function() {
    var $gross;

    resetRewardPoints();

    for(var i in $rewardRules){
      if($rewardRules[i].member && $('#user-id-value').val() == ''){
        break;
      }

      switch($rewardRules[i].type){
        case 'per_order':
          $gross = getTotalOrderBeforeExtras();
          if($rewardRules[i].rule.include_shipping){
            $gross += $orderShippingTotal;
          }
          if($rewardRules[i].rule.include_tax){
            $gross += $orderTaxTotal;
          }

          $rewardRules[i].total += Math.floor($gross/$rewardRules[i].rule.order_step_amount) * $rewardRules[i].reward;
          break;
      }
    }
  }

  var printRewardRules = function() {
    for(var i in $rewardRules){
      if($rewardRules[i].total > 0){
        if($('.reward-rule[data-reward_rule_id="'+$rewardRules[i].id+'"]', '#reward-points-wrapper').length < 1){
          $('.portlet-body', '#reward-points-wrapper').append($rewardRulePrototype({
            'id': $rewardRules[i].id,
            'name': $rewardRules[i].name,
            'reward': 0
          }));
        }

        $('.reward-rule[data-reward_rule_id="'+$rewardRules[i].id+'"] .amount', '#reward-points-wrapper').text($rewardRules[i].total);
      }
    }
  }

  var labelStatus = function($label, $newQuantity)
  {
    if($label.hasClass('stock-total')){
      if($newQuantity == 0){
        $label.parent().removeClass('label-info label-danger').addClass('label-warning');
      }else if($newQuantity < 0){
        $label.parent().removeClass('label-info label-warning').addClass('label-danger');
      }else{
        $label.parent().removeClass('label-danger label-warning').addClass('label-info');
      }
    }else{
      if($newQuantity == $label.parent().find('.limit-total').text()){
        $label.parent().removeClass('label-info label-danger').addClass('label-warning');
      }else if($newQuantity > $label.parent().find('.limit-total').text()){
        $label.parent().removeClass('label-info label-warning').addClass('label-danger');
      }else{
        $label.parent().removeClass('label-danger label-warning').addClass('label-info');
      }
    }

    $label.text(formHelper.roundNumber($newQuantity));
  }

  return {
    //main function to initiate the module
    resize: function(){

    },
    init: function () {
      handleBillingEmail();
      handleButtons();
      handleTaxPriceRuleRewardRules();
      handleAvailability();

      $totalShippingLineItems = $('.line-item[data-line_item="shipping"]', '#line-items-table').length;
      toggleAddShippingButton();

      $('.line-item, .child-line-item-header, .child-line-item', '#line-items-table').each(function(idx, obj){
          OrderForm.lineItemInit($(obj));
      });

      /*
      $('#billing-information-wrapper').on('address.change', function(){
        $('#order-form').trigger('order.major_change');
      });
      */

      $('#shipping-information-wrapper').on('address.change', function(){
        $('#order-form').trigger('order.major_change');
      });

      $('#order-form').trigger('order.major_change');
      $('#order-form').trigger('order.delivery_date_change');
    },
    lineItemInit: function(lineItem)
    {
      var $lineItem = lineItem.filter('.line-item:not(.child-line-item), .child-line-item-header, .child-line-item');

      if($lineItem.length > 1){
        $lineItem.each(function(idx, obj){
            OrderForm.lineItemInit($(obj));
        });
      }else{
        var $lineItemType = $lineItem.data('line_item');
        var $timestamp = generateUUID();
        $lineItem.attr('data-uid', $timestamp);
        $lineItems[$timestamp] = {
          uid: $timestamp,
          object: $lineItem,
          cartPriceRules: [],
          taxes: {},
          total: 0,
          net: 0,
          calculated: 0,
          base: Number($lineItem.find('.net-price-field').inputmask('unmaskedvalue'))
        };

        $('.product-search', $lineItem).bind('typeahead:select', function(e, suggestion){
          App.blockUI({
            target: $lineItem,
            boxed: true,
            message: 'Loading product...'
          });

            $.ajax(global_vars.product_line_item + '/' + suggestion.id, {
              method: 'POST',
              data: 'product_index=' + $lineItem.data('line_item_key') + '&isParent=' + (typeof $lineItem.data('parent_line_item_key') == 'undefined'?1:0) + '&parent_index=' + $lineItem.data('parent_line_item_key') + '&parent_product='+$lineItem.data('parent_product')+'&composite=' + $lineItem.data('composite'),
              success: function(data){
                App.unblockUI($lineItem);

                var $row = $(data.data);

                if($lineItem.hasClass('.line-item')){
                  $($('[data-parent_line_item_key="'+$lineItem.data('line_item_key')+'"]'), '#line-items-table').remove();
                }
                $lineItem.replaceWith($row);

                formBehaviors.init($row);
                OrderForm.lineItemInit($row);

                $row.find('.net-price-field').trigger('change');

                //Get product availability
                $.ajax(global_vars.get_product_availability + '/' + suggestion.id, {
                  method: 'POST',
                  data: $('#order-form').serialize(),
                  success: function(data){
                    handleLoadedAvailability(data.data, $row);
                    handleCategoryOrderLimit($row);
                  }
                });

                $('#order-form').trigger('order.major_change');
              }
            });
        });

        $('.line-item-remove', $lineItem).on('click', function(e){
          e.preventDefault();

          if($lineItem.hasClass('line-item') && typeof $lineItem.data('line_item_key') != 'undefined'){
            $('[data-parent_line_item_key="'+$lineItem.data('line_item_key')+'"]', '#line-items-table').each(function(idx, obj){
              $(obj).trigger('order.line_item_remove');
              $(obj).remove();
            });
          }

          $lineItem.trigger('order.line_item_remove');
          $lineItem.remove();

          if($lineItemType == 'shipping'){
            $totalShippingLineItems -= 1;
            toggleAddShippingButton();
          }

          $('#order-form').trigger('order.major_change');
        });

        $('.quantity-field, .net-price-field', $lineItem).each(function(idx, obj){
          var $totalAmount;
          var $netPrice;

          $(obj).on('change', function(e, stopImmediateTrigger){
            $netPrice = Number($lineItem.find('.net-price-field').inputmask('unmaskedvalue'));
            calculateLineItemNetPrice($lineItems[$lineItem.data('uid')], $netPrice, $lineItem.find('.quantity-field').val());
            $totalAmount = $lineItem.find('.quantity-field').val() * $netPrice;
            $totalAmount = formHelper.roundNumber($totalAmount);

            $lineItem.find('.lineitem-total-amount').val($totalAmount);

            if(!stopImmediateTrigger){
              $lineItem.find('.lineitem-total-amount').trigger('change');
            }
          });
        });

        $('.quantity-field', $lineItem).each(function(idx, obj){
          var newQuantity, newStock;

          $(obj).on('focus', function(e){
            $(obj).data('beforeValue', e.target.value);
          });

          $(obj).on('change', function(e){
            if(typeof $orderedTotal[$lineItem.find('.line-item-id').val()] != 'undefined'){
              newQuantity = $orderedTotal[$lineItem.find('.line-item-id').val()].ordered_total + Number($(obj).val());
              labelStatus($lineItem.find('.ordered-total'), formHelper.roundNumber(newQuantity));

              newStock = $orderedTotal[$lineItem.find('.line-item-id').val()].stock - Number($(obj).val());
              labelStatus($lineItem.find('.stock-total'), formHelper.roundNumber(newStock));

              handleCategoryOrderLimit($lineItem);
            }
          });
        });

        $lineItem.on('order.line_item_remove', function(e){
          var $lineItem = $(e.target);
          $lineItem.find('.quantity-field').trigger('focus').val(0);
          $lineItem.find('.quantity-field').trigger('change');

          for(var i in $cartPriceRules){
            $cartPriceRules[i].applied_line_items = $.grep($cartPriceRules[i].applied_line_items, function(n, i){
              return n.uid != $lineItem.data('uid');
            });
          }

          for(var i in $categoryOrderLimits){
            $categoryOrderLimits[i].lineitems = $.grep($categoryOrderLimits[i].lineItems, function(n, i){
              return n.uid != $lineItem.data('uid');
            });
          }

          delete $lineItems[$lineItem.data('uid')];
        });

        $lineItem.find('.lineitem-total-amount').change(function(e){
          calculateOrderSummary();
          printOrderSummary();

          calculateRewardRules();
          printRewardRules();
        });

        handleChildButtons($lineItem);
      }
    }
  };
}();

jQuery(document).ready(function() {
  OrderForm.init();
});

jQuery(window).resize(function() {
  OrderForm.resize();
});