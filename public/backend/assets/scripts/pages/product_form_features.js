var ProductFormFeatures = function(){
    var handleFeatures = function(context){
        $('#product-feature-add-btn', context).click(function(e){
            e.preventDefault();

            App.blockUI({
                target: '#tab_features',
                boxed: true,
                message: 'Adding features...'
            });

            $.ajax($('#product-features-form-wrapper').data('feature_index'), {
                data: $('#product-features-wrapper :input').serialize() + '&_token=' + $('#product-features-form-wrapper').data('form_token'),
                method: 'post',
                success: function(data){
                    var $productFeaturesIndex = $(data.html);

                    ProductFormFeatures.init($productFeaturesIndex);

                    $('#product-features-wrapper').html($productFeaturesIndex);
                },
                error: function(){
                    alert('An error occured. Please refresh this page.');
                },
                complete: function(){
                    App.unblockUI('#tab_features');
                }
            });
        });

        $('.feature-remove-btn', context).click(function(e){
            e.preventDefault();

            App.blockUI({
                target: '#tab_features',
                boxed: true,
                message: 'Removing feature...'
            });

            $.ajax($('#product-features-form-wrapper').data('feature_index'), {
                data: $('#product-features-wrapper :input').serialize()+'&remove_feature='+$(this).data('feature_id')+'&_token=' + $('#product-features-form-wrapper').data('form_token'),
                method: 'post',
                success: function(data){
                    var $productFeaturesIndex = $(data.html);

                    ProductFormFeatures.init($productFeaturesIndex);

                    $('#product-features-wrapper').html($productFeaturesIndex);
                },
                error: function(){
                    alert('An error occured. Please refresh this page.');
                },
                complete: function(){
                    App.unblockUI('#tab_features');
                }
            });
        });
    }

    return {
        init : function(context){
            if(typeof context === 'undefined'){
                context = document;
            }

            handleFeatures(context);
        }
    };
}();

jQuery(document).ready(function() {
    ProductFormFeatures.init();
});