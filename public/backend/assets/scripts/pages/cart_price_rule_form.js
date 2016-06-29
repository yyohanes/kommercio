var cartPriceRuleForm = function (){
    var newPriceRuleOptionGroupCount = $('#price-rule-option-groups-wrapper .price-rule-option-group').length;

    processNewPriceRuleOptionGroup = function(){
        newPriceRuleOptionGroupCount += 1;
        var $newPriceRuleOptionGroup = $($priceRuleOptionGroupMockup);

        $newPriceRuleOptionGroup.find('[name]').each(function(idx, obj){
            $(obj).attr('name', $(obj).attr('name').replace('[0]', '['+newPriceRuleOptionGroupCount+']'));

            var $attr = $(obj).attr('id');

            if (typeof $attr !== typeof undefined && $attr !== false) {
                var $newId = $attr.replace('[0]', '['+newPriceRuleOptionGroupCount+']');

                $newPriceRuleOptionGroup.find('label[for="'+$attr+'"]').attr('for', $newId);
                $(obj).attr('id', $newId);
            }
        });

        formBehaviors.init($newPriceRuleOptionGroup);

        $newPriceRuleOptionGroup.appendTo('#price-rule-option-groups-wrapper');
        App.scrollTo($newPriceRuleOptionGroup, 1);
    }

    handlePriceRuleOptions = function(){
        $('#price-rule-options-add').click(function(e){
            e.preventDefault();

            processNewPriceRuleOptionGroup();
        });
    }

    return {
        init: function(){
            handlePriceRuleOptions();
        }
    }
}();

jQuery(document).ready(function() {
    cartPriceRuleForm.init();
});