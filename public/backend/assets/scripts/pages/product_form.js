var ProductForm = function () {
    var categoriesCheckbox = function () {
        $('#categories-checkbox input[type="checkbox"]').on('change', function(){
            refreshDefaultCategory();
        });
    }

    var $defaultCategoryOptions;
    var $currentCheckbox = $('#default_category').data('default');
    var refreshDefaultCategory = function(){
        $defaultCategoryOptions = '';

        var $checkbox;
        $('#categories-checkbox label').each(function(idx, obj){
            $checkbox = $(obj).find('input[type="checkbox"]');
            if($checkbox.is(':checked')){
                $defaultCategoryOptions += '<option '+(($currentCheckbox == $checkbox.val())?'selected':'')+' value="'+$checkbox.val()+'">'+$(obj).find('.checkbox-label').text()+'</option>';
            }
        });

        $('#default_category').html($defaultCategoryOptions);
        $('#default_category').change();
    }

    $('#default_category').on('change', function(){
        $currentCheckbox = $(this).val();
    });

    var handleProductRelation = function(){
        $('.product-relation-finder', '#tab_related').each(function(idx, obj){
            var $productRelationZone = $('#' + $(obj).data('product_relation_type') + '-products');
            $productRelationZone.sortable({
                placeholder: '<div class="col-md-3 product-item"></div>'
            });

            $(obj).bind('typeahead:select', function(e, suggestion){
                $(obj).typeahead('val','');

                $.ajax(global_vars.get_related_product + '/' + suggestion.id + '/' + $(obj).data('product_relation_type'), {
                    method: 'GET',
                    success: function(data){
                        if($('#' + $(obj).data('product_relation_type') + '-products').find('[data-product_id="'+suggestion.id+'"]').length < 1){
                            var $loaded = $(data.data);
                            $('#' + $(obj).data('product_relation_type') + '-products').append($loaded);

                            handleLoadedProductRelation($loaded);
                            $productRelationZone.sortable('reload');
                        }
                    }
                });
            });

            handleLoadedProductRelation($('#' + $(obj).data('product_relation_type') + '-products'));
        });
    }

    var handleCompositeProductSearch = function($obj){
        var $productConfigurationZone = $obj.parents('.composite-configuration-products').find('.configuration-products');
        $productConfigurationZone.sortable({
            placeholder: '<div class="col-md-3 product-item"></div>'
        });

        $obj.bind('typeahead:select', function(e, suggestion){
            $obj.typeahead('val','');

            $.ajax(global_vars.get_related_product + '/' + suggestion.id + '/' + $obj.data('product_relation_type'), {
                method: 'GET',
                success: function(data){
                    if($productConfigurationZone.find('[data-product_id="'+suggestion.id+'"]').length < 1){
                        var $loaded = $(data.data);
                        $productConfigurationZone.append($loaded);

                        handleLoadedProductRelation($loaded);
                        $productConfigurationZone.sortable('reload');
                    }
                }
            });
        });

        handleLoadedProductRelation($productConfigurationZone);
    }

    var handleLoadedProductRelation = function(context){
        $('.product-item-remove', context).on('click', function(e){
            e.preventDefault();

            $(this).parent().remove();
        });
    }

    var handleCompositeProducts = function()
    {
        var compositeConfigurationCount = $('#composite-configurations-wrapper .composite-configuration').length;

        //Product configurations
        $('#product-configuration-add-btn').click(function(e){
            e.preventDefault();

            var $newProductConfiguration = $($compositeConfigurationMockup);

            $newProductConfiguration.find('[name]').each(function(idx, obj){
                $(obj).attr('name', $(obj).attr('name').replace('[0]', '['+compositeConfigurationCount+']'));

                var $attr = $(obj).attr('id');

                if (typeof $attr !== typeof undefined && $attr !== false) {
                    var $newId = $attr.replace('[0]', '['+compositeConfigurationCount+']');

                    $newProductConfiguration.find('label[for="'+$attr+'"]').attr('for', $newId);
                    $(obj).attr('id', $newId);
                }
            });

            $newProductConfiguration.find('[for]').each(function(idx, obj){
                $(obj).attr('for', $(obj).attr('for').replace('[0]', '['+compositeConfigurationCount+']'));
            });

            $newProductConfiguration.find('[data-product_relation_type]').each(function(idx, obj){
                $(obj).attr('data-product_relation_type', $(obj).attr('data-product_relation_type').replace('_0', '_'+compositeConfigurationCount));
            });

            formBehaviors.init($newProductConfiguration);

            $newProductConfiguration.appendTo('#composite-configurations-wrapper');
            $('#composite-configurations-wrapper').sortable('refresh');

            handleCompositeProductSearch($newProductConfiguration.find('.product-configuration-finder'));

            compositeConfigurationCount += 1;
        });

        $('.product-configuration-finder', '#tab_composite').each(function(idx, obj){
            handleCompositeProductSearch($(obj));
        });
    }

    return {

        //main function to initiate the module
        init: function () {
          var $variationContext = $('[data-tab_context="variations"]');
          var $attributesContext = $('[data-tab_context="attributes"]');

          $('#combination_type').on('change', function(){
            if($(this).val() == 'variable'){
              $variationContext.show();
              $attributesContext.hide();
            }else{
              $variationContext.hide();
              $attributesContext.show();
            }
          }).change();

          $('#product-variation-add-btn').click(function(e){
            e.preventDefault();
            e.stopPropagation();

            variationFormBehaviors.loadForm('?new_form');
          });

          $('#bulk-variation-add-btn').click(function(e){
            e.preventDefault();
            e.stopPropagation();

            variationFormBehaviors.loadForm('?edit_form', 'Loading edit form...', $(this).data('variation_bulk_form'));
          });

          handleCompositeProducts();

          refreshDefaultCategory();

          categoriesCheckbox();

          handleProductRelation();

          variationFormBehaviors.init();

          $("#composite-configurations-wrapper").sortable();
        }

    };
}();

var variationFormBehaviors = function(){
    var $variationFormUrl = $('#product-variation-form-wrapper').data('variation_form');

    var handleActions = function(context){
        $('.remove-attribute-btn', context).click(function(e){
            e.preventDefault();

            variationFormBehaviors.loadForm($('#product-variation-form-wrapper :input').serialize() + '&variation[remove_attribute]=' + $(this).data('attribute'), 'Removing attribute...', $('#product-variation-form-accordion').data('variation_edit'));
        });

        $('#add-new-attribute-btn', context).click(function(e){
            e.preventDefault();

            variationFormBehaviors.loadForm($('#product-variation-form-wrapper :input').serialize(), 'Adding attribute...', $('#product-variation-form-accordion').data('variation_edit'));
        });

        $('#variation-cancel', context).click(function(e){
            e.preventDefault();

            variationFormBehaviors.closeForm();
        });

        $('#variation-save', context).click(function(e){
            e.preventDefault();

            $('[data-inputmask]', '#product-variation-form-wrapper').inputmask('remove');
            formHelper.clearFormError({
                'wrapper': '#product-variation-form-wrapper',
                'highlightParentPrefix': 'panel',
                'messagesWrapper': '#variation-form-messages'
            });

            App.blockUI({
                target: '#tab_variations',
                boxed: true,
                message: 'Saving variation...'
            });

            $.ajax($(this).data('variation_save'), {
                'method': 'POST',
                'data': $('#product-variation-form-wrapper :input').serialize(),
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

                        variationFormBehaviors.closeForm();
                        variationFormBehaviors.refreshVariationIndex();
                    }
                },
                'error': function(xhr){
                    for(var i in xhr.responseJSON){
                        var $errorName = formHelper.convertDotToSquareBracket(i);
                        formHelper.addFieldError({
                            'name': $errorName,
                            'message': xhr.responseJSON[i][0],
                            'context': '#product-variation-form-wrapper',
                            'highlightParentPrefix': 'panel',
                            'messagesWrapper': '#variation-form-messages'
                        });

                        App.scrollTo($('#product-variation-form-wrapper'));
                    }

                    formBehaviors.initComponents(context);
                }
            });
        });

        $('[data-variation_delete]', context).on('click', function (e) {
            e.preventDefault();
        });

        $('.variation-edit-btn', context).on('click', function(e){
            e.preventDefault();

            variationFormBehaviors.loadForm('?edit_form', 'Loading edit form...', $(this).data('variation_edit'));
        });
    }

    return {
        init: function(context){
            if(typeof context === 'undefined'){
                context = document;
            }

            handleActions(context);

            $(document).ajaxComplete(function( event,request, settings ) {
                App.unblockUI('#tab_variations');
            });
        },
        loadForm: function(formData, message, formUrl){
            if(typeof message === 'undefined'){
                message = 'Loading form...';
            }

            if(typeof formUrl === 'undefined'){
                formUrl = $variationFormUrl;
            }

            $('#product-variation-form-wrapper').removeData('variation_form');
            $('#product-variation-form-wrapper').attr('data-variation_form', formUrl);

            App.blockUI({
                target: '#tab_variations',
                boxed: true,
                message: message
            });

            $.ajax(formUrl, {
                'method': 'POST',
                'data': formData,
                'success': function(data){
                    var $variationForm = $(data.html);

                    $('#product-variation-form-wrapper').html($variationForm);
                    App.unblockUI('#tab_variations');

                    formBehaviors.init($variationForm);
                    variationFormBehaviors.init($variationForm);
                    App.initAjax();
                },
                'error': function(){
                    alert('An error occured. Please refresh this page.');
                }
            });
        },
        closeForm: function()
        {
            $('#product-variation-form-wrapper').empty();
            $('#product-variation-form-wrapper').attr('data-variation_form', $variationFormUrl);
        },
        refreshVariationIndex: function()
        {
            $.ajax($('#product-variation-form-wrapper').data('variation_index'), {
                'method': 'GET',
                'success': function(data){
                    var $variationIndex = $(data.html);

                    $('#product-variations-wrapper').html($variationIndex);

                    formBehaviors.init($variationIndex);
                    variationFormBehaviors.init($variationIndex);
                    App.initAjax();
                }
            });
        },
        deleteVariation: function()
        {
            App.blockUI({
                target: '#tab_variations',
                boxed: true,
                message: 'Deleting variation...'
            });

            $.ajax($(this).data('variation_delete'), {
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

                    variationFormBehaviors.refreshVariationIndex();
                }
            });
        }
    }
}();

jQuery(document).ready(function() {
    ProductForm.init();
});