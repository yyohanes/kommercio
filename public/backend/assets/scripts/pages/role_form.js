var RoleForm = function () {
    var handleCheckAll = function()
    {
        $('.permissions-check-all').click(function(e){
            e.preventDefault();

            $(this).parent().next('.permissions-group').find('input[type="checkbox"]').prop('checked', true);
            App.updateUniform();
        });

        $('.permissions-uncheck-all').click(function(e){
            e.preventDefault();

            $(this).parent().next('.permissions-group').find('input[type="checkbox"]').prop('checked', false);
            App.updateUniform();
        });
    }

    return {
        //main function to initiate the module
        init: function () {
            handleCheckAll();
        }
    };
}();

jQuery(document).ready(function() {
    RoleForm.init();
});