var MenuIndex = function () {

    var initNestable = function () {
        $('#menu-items').nestable().on('change', function(e){
            var list = $(e.target);

            App.blockUI({
                target: '#menu-items',
                boxed: true,
                message: 'Saving...'
            });

            $.ajax(global_vars.reorder_path, {
                method: 'POST',
                data: {'objects': list.nestable('serialize')},
                success: function(data){
                    App.unblockUI('#menu-items');
                }
            });
        });
    }

    return {
        //main function to initiate the module
        init: function () {
            initNestable();
        }

    };

}();

jQuery(document).ready(function() {
    MenuIndex.init();
});