var ProductCompositeForm = function () {
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
        $('.product-configuration-finder').each(function(idx, obj){
            handleCompositeProductSearch($(obj));
        });
    }

    var handleDefaultProductSearch = function($obj){
        var $productConfigurationZone = $obj.parents('.composite-default-products').find('.default-products');
        $productConfigurationZone.sortable({
            placeholder: '<div class="col-md-3 product-item"></div>'
        });

        $obj.bind('typeahead:select', function(e, suggestion){
            $obj.typeahead('val','');

            $.ajax(global_vars.get_related_product + '/' + suggestion.id + '/' + $obj.data('product_relation_type') + '?template=catalog.product_composite.default_product_result', {
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

    var handleDefaultProducts = function()
    {
        $('.product-default-finder').each(function(idx, obj){
            handleDefaultProductSearch($(obj));
        });
    }

    return {

        //main function to initiate the module
        init: function () {
          handleCompositeProducts();
          handleDefaultProducts();
        }
    };
}();

jQuery(document).ready(function() {
    ProductCompositeForm.init();
});