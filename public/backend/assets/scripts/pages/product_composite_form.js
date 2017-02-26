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

    var handleDefaultProducts = function()
    {
        var $defaultSelect = $('.default-products-select');
        var $dependencies = ['composite_product', 'product_category'];

        var $source;
        var $param = {};

        $defaultSelect.select2({
            width: "off",
            ajax: {
                url: function(){
                    $source = $defaultSelect.data('remote_source') + '?';

                    for(var i in $dependencies){
                        var arr = $('[name^="'+$dependencies[i]+'"]').map(function(){
                            return $(this).val();
                        }).get();
                        var paramName = $dependencies[i];

                        if(paramName == 'composite_product'){
                            paramName = 'product';
                        }

                        $param[paramName] = arr;
                    }

                    return $source + $.param($param);
                },
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        query: params.term, // search term
                        page: params.page
                    };
                },
                processResults: function(data, page) {
                    // parse the results into the format expected by Select2.
                    // since we are using custom formatting functions we do not need to
                    // alter the remote JSON data
                    return {
                        results: data.data
                    };
                },
                cache: true
            },
            escapeMarkup: function(markup) {
                return markup;
            },
            minimumInputLength: 2,
            templateResult: function(repo){
                return repo[$defaultSelect.data('remote_label_property')];
            },
            templateSelection: function(repo){
                return repo[$defaultSelect.data('remote_value_property')] || repo.text;
            }
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