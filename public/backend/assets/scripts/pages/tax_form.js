var TaxForm = function () {

    var handleForm = function () {
        $('#country').on('change', function(e){
            $.ajax($(this).data('country_children')+'/'+$(this).val(), {
                success: function(data){
                    $('#country-children-wrapper').html(data);
                },
                error: function(xhr){
                    alert('An error occured. Please try again or refresh this page.');
                }
            });
        });
    }

    return {

        //main function to initiate the module
        init: function () {
            handleForm();
        }

    };

}();

jQuery(document).ready(function() {
    TaxForm.init();
});